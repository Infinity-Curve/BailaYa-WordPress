<?php
/**
 * Boots the plugin against a stub of WordPress and *fires the hooks*.
 *
 * `php -l` cannot see a missing `use` import, and merely requiring bailaya.php
 * does not either — the work happens inside closures registered on
 * `plugins_loaded`, `init` and `admin_menu`, which never run. That is exactly how
 *
 *     Fatal error: Class "UserProfileCard" not found
 *
 * reached a release: the file parsed, the plugin loaded, and the bug only
 * surfaced when WordPress actually called the callback.
 *
 * So this stubs the WordPress functions the plugin touches, requires the plugin,
 * and then invokes every callback it registered. Any unresolved class, bad
 * callable or typo blows up here instead of on a live site.
 *
 * Usage: php tools/smoke-test.php [path-to-plugin-dir]
 */
declare(strict_types=1);

$pluginDir = rtrim($argv[1] ?? dirname(__DIR__), '/\\');

define('ABSPATH', $pluginDir . '/');
define('WP_CONTENT_DIR', dirname($pluginDir));

/** @var array<string, list<callable>> */
$GLOBALS['__hooks'] = [];
$GLOBALS['__shortcodes'] = [];
$GLOBALS['__blocks'] = [];

function add_action(string $hook, $cb, int $priority = 10, int $args = 1): bool
{
    $GLOBALS['__hooks'][$hook][] = $cb;
    return true;
}
function add_filter(string $hook, $cb, int $priority = 10, int $args = 1): bool
{
    $GLOBALS['__hooks'][$hook][] = $cb;
    return true;
}
function add_shortcode(string $tag, $cb): void
{
    $GLOBALS['__shortcodes'][$tag] = $cb;
}
function register_block_type($path, array $args = []): bool
{
    if (!is_dir($path) || !is_readable("$path/block.json")) {
        throw new RuntimeException("register_block_type: no block.json at $path");
    }
    $json = json_decode((string)file_get_contents("$path/block.json"), true);
    if (!is_array($json) || empty($json['name'])) {
        throw new RuntimeException("register_block_type: invalid block.json at $path");
    }
    // The editor script must exist, or the block cannot be configured.
    if (!empty($json['editorScript']) && str_starts_with($json['editorScript'], 'file:')) {
        $rel = substr($json['editorScript'], strlen('file:./'));
        if (!is_readable("$path/$rel")) {
            throw new RuntimeException("block {$json['name']}: missing $rel (run `npm run build`)");
        }
    }
    $GLOBALS['__blocks'][] = $json['name'];
    return true;
}

// Everything else the plugin calls at load/registration time.
function register_activation_hook(...$a) {}
function register_deactivation_hook(...$a) {}
function plugin_dir_path(string $f): string { return dirname($f) . '/'; }
function plugin_dir_url(string $f): string { return 'https://example.test/wp-content/plugins/bailaya/'; }
function plugin_basename(string $f): string { return 'bailaya/' . basename($f); }
function plugins_url(string $p = '', string $f = ''): string { return 'https://example.test/' . ltrim($p, '/'); }
function load_plugin_textdomain(...$a): bool { return true; }
function wp_register_style(...$a): bool { return true; }
function wp_enqueue_style(...$a): void {}
function wp_add_inline_style(...$a): bool { return true; }
function add_options_page(...$a): string { return 'bailaya-settings'; }
function add_menu_page(...$a): string { return 'bailaya-classes'; }
function add_submenu_page(...$a): string { return 'bailaya-sub'; }
function register_setting(...$a): void {}
function add_settings_section(string $id, $title, $cb, string $page): void { if (is_callable($cb)) { ob_start(); $cb(); ob_end_clean(); } }
function add_settings_field(string $id, $title, $cb, string $page, string $section = 'default'): void { if (is_callable($cb)) { ob_start(); $cb(); ob_end_clean(); } }
function get_option(string $k, $d = false) { return $d; }
function esc_html(string $s): string { return htmlspecialchars($s, ENT_QUOTES); }
function esc_attr(string $s): string { return htmlspecialchars($s, ENT_QUOTES); }
function esc_url(string $s): string { return $s; }
function esc_url_raw(string $s): string { return $s; }
function esc_html__(string $s, string $d = ''): string { return $s; }
function esc_attr__(string $s, string $d = ''): string { return $s; }
function esc_textarea(string $s): string { return $s; }
function __(string $s, string $d = ''): string { return $s; }
function _e(string $s, string $d = ''): void { echo $s; }
function wp_kses(string $s, array $allowed): string { return $s; }
function checked($a, $b = true, bool $echo = true): string { return $a === $b ? 'checked' : ''; }
function selected($a, $b = true, bool $echo = true): string { return $a === $b ? 'selected' : ''; }
function submit_button(...$a): void {}
function settings_fields(...$a): void {}
function do_settings_sections(...$a): void {}
function wp_dropdown_roles(string $s = ''): void {}
function wp_roles() { return new class { public function get_names(): array { return ['subscriber' => 'Subscriber', 'administrator' => 'Administrator']; } }; }
function get_role(string $role) {
    // Enough of WP_Role to exercise Helpers::is_role_privileged(): Administrator
    // holds the privileged caps, Subscriber holds none.
    return match ($role) {
        'administrator' => new class { public function has_cap(string $cap): bool { return true; } },
        'subscriber' => new class { public function has_cap(string $cap): bool { return false; } },
        default => null,
    };
}
function translate_user_role(string $name, string $d = ''): string { return $name; }
function get_current_user_id(): int { return 1; }
function delete_transient(string $k): bool { return true; }
function admin_url(string $p = ''): string { return 'https://example.test/wp-admin/' . $p; }
function home_url(string $p = ''): string { return 'https://example.test' . $p; }
function wp_login_url(): string { return 'https://example.test/wp-login.php'; }
function add_query_arg($a, $b = null, $c = null): string { return 'https://example.test/?x=1'; }
function sanitize_text_field(string $s): string { return trim($s); }
function sanitize_key(string $s): string { return strtolower($s); }
function sanitize_email(string $s): string { return $s; }
function sanitize_user(string $s, bool $strict = false): string { return $s; }
function wp_unslash($s) { return $s; }
function current_user_can(string $c): bool { return true; }
function get_locale(): string { return 'en_US'; }
function get_transient(string $k) { return false; }
function set_transient(string $k, $v, int $t): bool { return true; }
function wp_generate_password(int $n = 12, bool $s = true): string { return str_repeat('x', $n); }
function wp_json_encode($v): string { return (string)json_encode($v); }

require $pluginDir . '/bailaya.php';

$errors = [];

// Fire every hook the plugin registered. This is the part that matters: it is
// where `new UserProfileCard()` actually executes.
foreach ($GLOBALS['__hooks'] as $hook => $callbacks) {
    foreach ($callbacks as $cb) {
        try {
            ob_start();
            $cb('');
            ob_end_clean();
        } catch (\Throwable $e) {
            if (ob_get_level() > 0) ob_end_clean();
            $errors[] = sprintf('%s: %s (%s)', $hook, $e->getMessage(), get_class($e));
        }
    }
}

printf("hooks fired:  %d\n", array_sum(array_map('count', $GLOBALS['__hooks'])));
printf("shortcodes:   %d  (%s)\n", count($GLOBALS['__shortcodes']), implode(', ', array_keys($GLOBALS['__shortcodes'])));
printf("blocks:       %d\n", count($GLOBALS['__blocks']));

if ($errors) {
    echo "\nFAILED:\n";
    foreach ($errors as $e) echo "  ✗ $e\n";
    exit(1);
}

// The plugin must actually register what it claims to.
$expectedShortcodes = [
    'bailaya_class_schedule', 'bailaya_class_schedule_by_type', 'bailaya_events',
    'bailaya_instructors', 'bailaya_studio_profile', 'bailaya_user_profile',
    'bailaya_private_lesson_instructors', 'bailaya_packages', 'bailaya_locations',
];
$missing = array_diff($expectedShortcodes, array_keys($GLOBALS['__shortcodes']));
if ($missing) {
    echo "\nFAILED: shortcodes never registered: " . implode(', ', $missing) . "\n";
    exit(1);
}

// readme.txt must advertise the tags the plugin actually registers — a shortcode
// documented under the wrong name is a support ticket waiting to happen.
$readme = (string)file_get_contents($pluginDir . '/readme.txt');
preg_match_all('/\[(bailaya_[a-z_]+)/', $readme, $m);
$documented = array_unique($m[1]);
$phantom = array_diff($documented, array_keys($GLOBALS['__shortcodes']));
if ($phantom) {
    echo "\nFAILED: readme.txt documents shortcodes that do not exist: "
        . implode(', ', $phantom) . "\n";
    exit(1);
}
if (count($GLOBALS['__blocks']) !== 9) {
    echo "\nFAILED: expected 9 blocks, registered " . count($GLOBALS['__blocks']) . "\n";
    exit(1);
}

// "Sign in with BailaYa" creates accounts for people who are not logged in, so no
// privileged role may ever be on offer for them — not in the settings dropdown, and
// not as the role handed to wp_insert_user().
$assignable = array_keys(BailaYaWP\Helpers::assignable_roles());
if (in_array('administrator', $assignable, true)) {
    echo "\nFAILED: administrator is offered as an auto-provisioned OAuth role\n";
    exit(1);
}
$defaultRole = BailaYaWP\Helpers::oauth_default_role();
if ($defaultRole !== '' && BailaYaWP\Helpers::is_role_privileged($defaultRole)) {
    echo "\nFAILED: OAuth users would be created with the privileged role '$defaultRole'\n";
    exit(1);
}

echo "\n  plugin boots, all hooks fire, all shortcodes and blocks register\n";
