<?php
declare(strict_types=1);

namespace BailaYaWP\Auth;

use BailaYaWP\ClientFactory;
use BailaYaWP\Helpers;

if (!defined('ABSPATH')) exit;

/**
 * "Sign in with BailaYa" — logs WordPress users in against BailaYa's OpenID
 * Connect provider using the authorization-code flow with PKCE (S256).
 *
 * Two front-controller routes hang off `init`:
 *   /?bailaya_oauth=start     starts the flow and redirects to BailaYa
 *   /?bailaya_oauth=callback  the redirect URI BailaYa sends the user back to
 *
 * The PKCE verifier and `state` live in a short-lived transient keyed by the
 * state value, so nothing sensitive rides in the URL or a cookie.
 *
 * On nonce verification: the $_GET reads below are the OAuth redirect coming back
 * from the provider, plus the login-screen link that starts the flow. A WordPress
 * nonce cannot be used here — the request originates at BailaYa, not at a form we
 * rendered, and the visitor is by definition not yet logged in. The equivalent CSRF
 * defence is the `state` parameter: it is a single-use, server-generated value held
 * in a 10-minute transient alongside the PKCE verifier, and callback() rejects any
 * response whose state does not match. Every value read is unslashed and sanitized,
 * and none of them mutate state on their own.
 */
// phpcs:disable WordPress.Security.NonceVerification.Recommended
final class SignInWithBailaya
{
    private const QUERY_VAR = 'bailaya_oauth';
    private const TRANSIENT_PREFIX = 'bailaya_oauth_';
    private const STATE_TTL = 600; // 10 minutes
    private const SCOPE = 'openid profile email';

    public function register(): void
    {
        add_action('init', [$this, 'handleRoutes']);
        add_action('login_form', [$this, 'renderLoginButton']);
        add_action('login_enqueue_scripts', [$this, 'renderLoginStyles']);
        add_filter('login_message', [$this, 'renderLoginError']);
    }

    /** Surfaces the reason a sign-in attempt bounced back to wp-login. */
    public function renderLoginError(string $message): string
    {
        if (!isset($_GET['bailaya_oauth_error'])) {
            return $message;
        }

        $error = sanitize_text_field(wp_unslash((string)$_GET['bailaya_oauth_error']));

        return $message . sprintf(
            '<div id="login_error">%s</div>',
            esc_html($error)
        );
    }

    /** Whether the feature is switched on and configured. */
    public function isEnabled(): bool
    {
        return (bool)Helpers::get_option('oauth_enabled', false)
            && (string)Helpers::get_option('oauth_client_id', '') !== '';
    }

    /** The redirect URI that must be registered with the BailaYa OAuth client. */
    public static function redirectUri(): string
    {
        return add_query_arg(self::QUERY_VAR, 'callback', home_url('/'));
    }

    // ─── Login screen ──────────────────────────────────────────────────────────

    public function renderLoginButton(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $redirectTo = isset($_GET['redirect_to'])
            ? sanitize_text_field(wp_unslash((string)$_GET['redirect_to']))
            : '';

        $url = add_query_arg(
            array_filter([
                self::QUERY_VAR => 'start',
                'redirect_to' => $redirectTo ?: null,
            ]),
            home_url('/')
        );

        printf(
            '<p class="bailaya-oauth"><a class="button button-primary button-large" href="%s">%s</a></p>',
            esc_url($url),
            esc_html__('Sign in with BailaYa', 'bailaya')
        );
    }

    /**
     * Styles the sign-in button on the login screen.
     *
     * Registered and attached as an inline style rather than echoed as a raw
     * <style> tag, so it goes through the stylesheet queue like everything else.
     */
    public function renderLoginStyles(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $handle = 'bailaya-login';

        wp_register_style($handle, false, [], BAILAYA_WP_VER);
        wp_enqueue_style($handle);
        wp_add_inline_style(
            $handle,
            '.bailaya-oauth { margin: 16px 0; text-align: center; }'
            . '.bailaya-oauth .button { width: 100%; text-align: center; }'
        );
    }

    // ─── Routes ────────────────────────────────────────────────────────────────

    public function handleRoutes(): void
    {
        $route = isset($_GET[self::QUERY_VAR])
            ? sanitize_key((string)$_GET[self::QUERY_VAR])
            : '';

        if ($route === '') {
            return;
        }
        if (!$this->isEnabled()) {
            $this->fail(__('Sign in with BailaYa is not enabled.', 'bailaya'));
        }

        if ($route === 'start') {
            $this->start();
        } elseif ($route === 'callback') {
            $this->callback();
        }
    }

    /** Builds the authorize URL, stashes the PKCE verifier, and redirects. */
    private function start(): void
    {
        if (is_user_logged_in()) {
            wp_safe_redirect(admin_url());
            exit;
        }

        $clientId = (string)Helpers::get_option('oauth_client_id', '');

        try {
            $oauth = ClientFactory::oauth();
            $pkce  = $oauth->createPkcePair();
            $state = wp_generate_password(32, false);

            set_transient(
                self::TRANSIENT_PREFIX . $state,
                [
                    'codeVerifier' => $pkce['codeVerifier'],
                    'redirectTo'   => isset($_GET['redirect_to'])
                        ? sanitize_text_field(wp_unslash((string)$_GET['redirect_to']))
                        : '',
                ],
                self::STATE_TTL
            );

            $url = $oauth->buildAuthorizeUrl([
                'clientId'      => $clientId,
                'redirectUri'   => self::redirectUri(),
                'scope'         => self::SCOPE,
                'state'         => $state,
                'codeChallenge' => $pkce['codeChallenge'],
            ]);
        } catch (\Throwable $e) {
            $this->fail($e->getMessage());
        }

        // The authorize endpoint is on the BailaYa host, which wp_safe_redirect()
        // would otherwise reject as off-site. Allowing exactly that one host keeps
        // the redirect bounded: if the base_url option were ever tampered with, the
        // redirect still cannot be pointed at an arbitrary third party.
        $host = wp_parse_url($url, PHP_URL_HOST);
        add_filter(
            'allowed_redirect_hosts',
            static function (array $hosts) use ($host): array {
                if (is_string($host) && $host !== '') {
                    $hosts[] = $host;
                }
                return $hosts;
            }
        );

        wp_safe_redirect($url);
        exit;
    }

    /** Verifies state, exchanges the code, and signs the matching WP user in. */
    private function callback(): void
    {
        if (isset($_GET['error'])) {
            $this->fail(sanitize_text_field(wp_unslash(
                (string)($_GET['error_description'] ?? $_GET['error'])
            )));
        }

        $code  = isset($_GET['code']) ? sanitize_text_field(wp_unslash((string)$_GET['code'])) : '';
        $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash((string)$_GET['state'])) : '';

        if ($code === '' || $state === '') {
            $this->fail(__('The BailaYa callback was missing its code or state.', 'bailaya'));
        }

        $stashed = get_transient(self::TRANSIENT_PREFIX . $state);
        if (!is_array($stashed) || empty($stashed['codeVerifier'])) {
            // Unknown, expired, or replayed state.
            $this->fail(__('This sign-in request has expired. Please try again.', 'bailaya'));
        }
        delete_transient(self::TRANSIENT_PREFIX . $state);

        $clientId     = (string)Helpers::get_option('oauth_client_id', '');
        $clientSecret = (string)Helpers::get_option('oauth_client_secret', '');

        try {
            $oauth = ClientFactory::oauth();

            $tokens = $oauth->exchangeCode(array_filter([
                'clientId'     => $clientId,
                'code'         => $code,
                'codeVerifier' => (string)$stashed['codeVerifier'],
                'redirectUri'  => self::redirectUri(),
                'clientSecret' => $clientSecret ?: null,
            ], fn($v) => $v !== null));

            $claims = $oauth->getUserInfo((string)$tokens['access_token']);
        } catch (\Throwable $e) {
            $this->fail($e->getMessage());
        }

        $user = $this->resolveUser($claims);

        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        // Core's own hook, not ours — other plugins rely on it firing on every login,
        // so it must keep its unprefixed name.
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        do_action('wp_login', $user->user_login, $user);

        /**
         * Fires after a user signs in through BailaYa, with the OIDC claims.
         *
         * @param \WP_User             $user
         * @param array<string,mixed>  $claims
         * @param array<string,mixed>  $tokens
         */
        do_action('bailaya_oauth_login', $user, $claims, $tokens);

        $redirectTo = (string)($stashed['redirectTo'] ?? '');
        wp_safe_redirect($redirectTo !== '' ? $redirectTo : admin_url());
        exit;
    }

    /**
     * Finds the WordPress user for these claims — by a previously linked BailaYa
     * subject first, then by email — creating one when auto-provisioning is on.
     *
     * @param array<string,mixed> $claims
     */
    private function resolveUser(array $claims): \WP_User
    {
        $sub   = (string)($claims['sub'] ?? '');
        $email = sanitize_email((string)($claims['email'] ?? ''));

        if ($sub === '') {
            $this->fail(__('BailaYa returned no subject claim.', 'bailaya'));
        }

        // Accounts are matched by email address, so an email the provider tells us
        // it has *not* verified must not be used: it would let someone claim an
        // existing WordPress account by registering that address at the provider.
        // A missing claim means the provider asserts nothing either way, which is
        // not the same as asserting false, and is left to the provider's policy.
        if (array_key_exists('email_verified', $claims)
            && !filter_var($claims['email_verified'], FILTER_VALIDATE_BOOLEAN)) {
            $this->fail(__('Your BailaYa email address is not verified.', 'bailaya'));
        }

        // Previously linked account. There is no core API for "find the user with this
        // external subject", so this is a meta lookup by necessity. It is bounded to a
        // single row and runs at most once per sign-in, not per page view.
        // phpcs:disable WordPress.DB.SlowDBQuery
        $linked = get_users([
            'meta_key'   => 'bailaya_sub',
            'meta_value' => $sub,
            'number'     => 1,
            'fields'     => 'ID',
        ]);
        // phpcs:enable WordPress.DB.SlowDBQuery
        if ($linked) {
            return new \WP_User((int)$linked[0]);
        }

        if ($email === '') {
            $this->fail(__('BailaYa returned no email address to match against.', 'bailaya'));
        }

        // Link an existing account with the same email.
        $existing = get_user_by('email', $email);
        if ($existing instanceof \WP_User) {
            update_user_meta($existing->ID, 'bailaya_sub', $sub);
            return $existing;
        }

        if (!Helpers::get_option('oauth_auto_create_users', false)) {
            $this->fail(__('No WordPress account matches your BailaYa email, and automatic account creation is off.', 'bailaya'));
        }

        // Helpers::oauth_default_role() re-checks the stored role's capabilities
        // rather than trusting the option: this account is being created for an
        // unauthenticated visitor, so it must never come out privileged, whatever
        // the option happens to say. A privileged role is downgraded to Subscriber.
        $userId = wp_insert_user([
            'user_login'   => $this->uniqueLogin($email),
            'user_email'   => $email,
            'user_pass'    => wp_generate_password(24),
            'first_name'   => sanitize_text_field((string)($claims['given_name'] ?? '')),
            'last_name'    => sanitize_text_field((string)($claims['family_name'] ?? '')),
            'display_name' => sanitize_text_field((string)($claims['name'] ?? $email)),
            'role'         => Helpers::oauth_default_role(),
        ]);

        if (is_wp_error($userId)) {
            $this->fail($userId->get_error_message());
        }

        update_user_meta((int)$userId, 'bailaya_sub', $sub);
        return new \WP_User((int)$userId);
    }

    /** Derives a login from the email, suffixed until it is free. */
    private function uniqueLogin(string $email): string
    {
        $base  = sanitize_user((string)strstr($email, '@', true), true) ?: 'bailaya_user';
        $login = $base;
        $i     = 1;

        while (username_exists($login)) {
            $login = $base . '_' . (++$i);
        }

        return $login;
    }

    /** Bounces back to wp-login with the reason shown to the user. */
    private function fail(string $message): never
    {
        wp_safe_redirect(add_query_arg(
            'bailaya_oauth_error',
            rawurlencode($message),
            wp_login_url()
        ));
        exit;
    }
}
