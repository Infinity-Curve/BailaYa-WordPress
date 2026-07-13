<?php
declare(strict_types=1);

namespace BailaYaWP\Admin;

use BailaYaWP\Auth\SignInWithBailaya;
use BailaYaWP\Helpers;

if (!defined('ABSPATH')) exit;

final class SettingsPage
{
    public function register(): void
    {
        add_action('admin_menu', function () {
            add_options_page(
                __('BailaYa Settings', 'bailaya'),
                __('BailaYa', 'bailaya'),
                'manage_options',
                'bailaya-settings',
                [$this, 'render']
            );
        });

        add_action('admin_init', function () {
            register_setting('bailaya_settings_group', 'bailaya_settings', [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
                'default' => [],
            ]);

            add_settings_section('bailaya_main', __('API Configuration', 'bailaya'), function () {
                echo '<p>' . esc_html__(
                    'Configure the BailaYa API connection used by the blocks and shortcodes.',
                    'bailaya'
                ) . '</p>';

                // This plugin is a front end for a third-party service: without a
                // BailaYa studio there is nothing to display, so say so here rather
                // than leaving an admin staring at empty blocks.
                printf(
                    '<p>%s</p>',
                    sprintf(
                        /* translators: %s: link to bailaya.com */
                        esc_html__(
                            'This plugin requires an account with BailaYa, a third-party dance studio management service. Your Studio ID comes from there. Sign up or sign in at %s.',
                            'bailaya'
                        ),
                        '<a href="https://www.bailaya.com" target="_blank" rel="noopener noreferrer">bailaya.com</a>'
                    )
                );
            }, 'bailaya-settings');

            add_settings_field('base_url', __('API Base URL', 'bailaya'), function () {
                printf(
                    '<input type="url" name="bailaya_settings[base_url]" value="%s" class="regular-text" />',
                    esc_attr((string)Helpers::get_option('base_url', 'https://www.bailaya.com/api'))
                );
            }, 'bailaya-settings', 'bailaya_main');

            add_settings_field('studio_id', __('Default Studio ID', 'bailaya'), function () {
                printf(
                    '<input type="text" name="bailaya_settings[studio_id]" value="%s" class="regular-text" />',
                    esc_attr((string)Helpers::get_option('studio_id', ''))
                );
                echo '<p class="description">' . wp_kses(
                    __('Can be overridden per shortcode via <code>override_id</code>.', 'bailaya'),
                    ['code' => []]
                ) . '</p>';
            }, 'bailaya-settings', 'bailaya_main');

            add_settings_field('cache_ttl', __('Cache TTL (seconds)', 'bailaya'), function () {
                printf(
                    '<input type="number" name="bailaya_settings[cache_ttl]" value="%d" min="0" step="1" />',
                    (int)Helpers::get_option('cache_ttl', 300)
                );
                echo '<p class="description">' . wp_kses(
                    __('0 disables caching.', 'bailaya'),
                    ['code' => []]
                ) . '</p>';
            }, 'bailaya-settings', 'bailaya_main');

            add_settings_section('bailaya_management', __('Management API (optional)', 'bailaya'), function () {
                echo '<p>' . esc_html__(
                    'Credentials for the authenticated Management API. The shortcodes and blocks above only use the public API and need none of this.',
                    'bailaya'
                ) . '</p>';
            }, 'bailaya-settings');

            add_settings_field('api_key', __('API Key', 'bailaya'), function () {
                printf(
                    '<input type="password" name="bailaya_settings[api_key]" value="%s" class="regular-text" autocomplete="off" />',
                    esc_attr((string)Helpers::get_option('api_key', ''))
                );
                echo '<p class="description">' . wp_kses(
                    __('Looks like <code>bya_live_…</code>. The key already identifies the studio.', 'bailaya'),
                    ['code' => []]
                ) . '</p>';
            }, 'bailaya-settings', 'bailaya_management');

            add_settings_field('access_token', __('Access Token', 'bailaya'), function () {
                printf(
                    '<input type="password" name="bailaya_settings[access_token]" value="%s" class="regular-text" autocomplete="off" />',
                    esc_attr((string)Helpers::get_option('access_token', ''))
                );
                echo '<p class="description">' . wp_kses(
                    __('A user session or OIDC access token. Used only when no API key is set.', 'bailaya'),
                    ['code' => []]
                ) . '</p>';
            }, 'bailaya-settings', 'bailaya_management');

            add_settings_section('bailaya_oauth', __('Sign in with BailaYa (optional)', 'bailaya'), function () {
                echo '<p>' . esc_html__(
                    'Let visitors sign in to WordPress with their BailaYa account (OpenID Connect, PKCE). Register this redirect URI with your BailaYa OAuth client:',
                    'bailaya'
                ) . '</p>';
                echo '<p><code>' . esc_html(SignInWithBailaya::redirectUri()) . '</code></p>';
            }, 'bailaya-settings');

            add_settings_field('oauth_enabled', __('Enable', 'bailaya'), function () {
                echo '<label><input type="checkbox" name="bailaya_settings[oauth_enabled]" value="1" ';
                checked((bool)Helpers::get_option('oauth_enabled', false));
                echo ' /> ' . esc_html__('Show a “Sign in with BailaYa” button on the login screen.', 'bailaya')
                    . '</label>';
            }, 'bailaya-settings', 'bailaya_oauth');

            add_settings_field('oauth_client_id', __('Client ID', 'bailaya'), function () {
                printf(
                    '<input type="text" name="bailaya_settings[oauth_client_id]" value="%s" class="regular-text" />',
                    esc_attr((string)Helpers::get_option('oauth_client_id', ''))
                );
            }, 'bailaya-settings', 'bailaya_oauth');

            add_settings_field('oauth_client_secret', __('Client Secret', 'bailaya'), function () {
                printf(
                    '<input type="password" name="bailaya_settings[oauth_client_secret]" value="%s" class="regular-text" autocomplete="off" />',
                    esc_attr((string)Helpers::get_option('oauth_client_secret', ''))
                );
                echo '<p class="description">' . wp_kses(
                    __('Confidential clients only. Leave blank for a public client (PKCE alone).', 'bailaya'),
                    ['code' => []]
                ) . '</p>';
            }, 'bailaya-settings', 'bailaya_oauth');

            add_settings_field('oauth_auto_create_users', __('Auto-create users', 'bailaya'), function () {
                echo '<label><input type="checkbox" name="bailaya_settings[oauth_auto_create_users]" value="1" ';
                checked((bool)Helpers::get_option('oauth_auto_create_users', false));
                echo ' /> ' . esc_html__('Create a WordPress account when no user matches the BailaYa email.', 'bailaya')
                    . '</label>';
                echo '<p class="description">' . wp_kses(
                    __('When off, only people who already have a WordPress account can sign in.', 'bailaya'),
                    ['code' => []]
                ) . '</p>';
            }, 'bailaya-settings', 'bailaya_oauth');

            add_settings_field('oauth_default_role', __('Role for new users', 'bailaya'), function () {
                // Deliberately NOT wp_dropdown_roles(), which offers Administrator.
                // These accounts are created by an unauthenticated visitor returning
                // from the OAuth callback, so only roles that cannot administer the
                // site, manage users or post unfiltered HTML are on offer.
                $current = Helpers::oauth_default_role();

                echo "<select name='bailaya_settings[oauth_default_role]'>";
                foreach (Helpers::assignable_roles() as $slug => $label) {
                    printf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr($slug),
                        selected($slug, $current, false),
                        esc_html($label)
                    );
                }
                echo '</select>';
                echo '<p class="description">' . esc_html__(
                    'Administrator and other privileged roles are not offered: these accounts are created automatically for anyone who signs in with BailaYa.',
                    'bailaya'
                ) . '</p>';
            }, 'bailaya-settings', 'bailaya_oauth');
        });
    }

    /** @param array $input */
    public function sanitize(array $input): array
    {
        // Only roles that are safe to auto-provision are accepted; anything else
        // (including a hand-posted "administrator") falls back to Subscriber.
        $assignable = array_keys(Helpers::assignable_roles());
        $role = isset($input['oauth_default_role']) ? sanitize_key($input['oauth_default_role']) : 'subscriber';

        return [
            'base_url' => isset($input['base_url']) ? esc_url_raw($input['base_url']) : 'https://www.bailaya.com/api',
            'studio_id' => isset($input['studio_id']) ? sanitize_text_field($input['studio_id']) : '',
            'cache_ttl' => isset($input['cache_ttl']) ? max(0, (int)$input['cache_ttl']) : 300,
            'api_key' => isset($input['api_key']) ? sanitize_text_field($input['api_key']) : '',
            'access_token' => isset($input['access_token']) ? sanitize_text_field($input['access_token']) : '',
            'oauth_enabled' => !empty($input['oauth_enabled']),
            'oauth_client_id' => isset($input['oauth_client_id']) ? sanitize_text_field($input['oauth_client_id']) : '',
            'oauth_client_secret' => isset($input['oauth_client_secret']) ? sanitize_text_field($input['oauth_client_secret']) : '',
            'oauth_auto_create_users' => !empty($input['oauth_auto_create_users']),
            'oauth_default_role' => in_array($role, $assignable, true) ? $role : 'subscriber',
        ];
    }

    public function render(): void
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('BailaYa Settings', 'bailaya'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('bailaya_settings_group');
                do_settings_sections('bailaya-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
