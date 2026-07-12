<?php
/**
 * Plugin Name:       BailaYa
 * Plugin URI:        https://www.bailaya.com/
 * Description:       Embed your BailaYa class schedules, events, instructors, packages and locations in WordPress with shortcodes and blocks.
 * Version:           1.8.3
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            Infinity Curve LLC
 * Author URI:        https://www.infinitycurve.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bailaya
 * Domain Path:       /languages
 *
 * BailaYa for WordPress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by the Free
 * Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <https://www.gnu.org/licenses/gpl-2.0.html>.
 */

if (!defined('ABSPATH')) exit;

// Keep in step with the "Version" header above and readme.txt's "Stable tag".
define('BAILAYA_WP_VER', '1.8.3');
define('BAILAYA_WP_PATH', plugin_dir_path(__FILE__));
define('BAILAYA_WP_URL', plugin_dir_url(__FILE__));

/**
 * Load translations.
 *
 * Plugin Check flags this call as discouraged since 4.6, but that guidance only
 * holds for translations delivered as wordpress.org language packs: those land in
 * WP_LANG_DIR/plugins/, which is one of the three directories core's just-in-time
 * loader actually scans (the others being WP_LANG_DIR/themes/ and any path
 * registered via WP_Textdomain_Registry::set_custom_path()).
 *
 * Nothing in core reads the "Domain Path" header when loading, so the .mo files we
 * bundle in languages/ are only found if we register that path ourselves — and
 * load_plugin_textdomain() is the only public API that does. Dropping this call
 * would silently disable all bundled locales on every WordPress version, 6.7+
 * included, until wordpress.org language packs exist for them.
 *
 * Hooked to `init` rather than `plugins_loaded` so 6.7 does not warn about
 * translations being loaded too early.
 */
add_action('init', function () {
    // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound
    load_plugin_textdomain('bailaya', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

/**
 * Composer autoload (prefer the plugin's own vendor/, then a site-wide one).
 *
 * Wrapped in a closure so the loop variables stay out of the global scope.
 */
(static function (): void {
    $candidates = [
        BAILAYA_WP_PATH . 'vendor/autoload.php',
        WP_CONTENT_DIR . '/vendor/autoload.php',
    ];

    foreach ($candidates as $candidate) {
        if (is_readable($candidate)) {
            require_once $candidate;
            return;
        }
    }
})();

use BailaYaWP\Admin\ManagementPage;
use BailaYaWP\Admin\SettingsPage;
use BailaYaWP\Auth\SignInWithBailaya;
use BailaYaWP\Shortcodes\ClassSchedule;
use BailaYaWP\Shortcodes\ClassScheduleByType;
use BailaYaWP\Shortcodes\EventSchedule;
use BailaYaWP\Shortcodes\InstructorList;
use BailaYaWP\Shortcodes\LocationList;
use BailaYaWP\Shortcodes\PackageList;
use BailaYaWP\Shortcodes\PrivateLessonInstructors;
use BailaYaWP\Shortcodes\StudioProfileCard;
use BailaYaWP\Shortcodes\UserProfileCard;

/** Activation/Deactivation (reserved for future migrations) */
register_activation_hook(__FILE__, function() {
    // No-op for now.
});
register_deactivation_hook(__FILE__, function() {
    // No-op for now.
});

/**
 * Register shared assets and blocks.
 * With @wordpress/scripts, point block.json to "file:./build/index.js"
 * so we don't manually register editor scripts here.
 */
add_action('init', function () {
    // Front-end style for both shortcodes and blocks
    wp_register_style(
        'bailaya-frontend-styles',
        plugins_url('assets/css/bailaya.css', __FILE__),
        [],
        BAILAYA_WP_VER
    );

    // Map the shortcode handler renderer:
    wp_register_style(
        'bailaya',
        plugins_url('assets/css/bailaya.css', __FILE__),
        [],
        BAILAYA_WP_VER
    );

    // Register blocks from metadata AFTER styles are registered
    register_block_type(__DIR__ . '/blocks/class-schedule');
    register_block_type(__DIR__ . '/blocks/class-schedule-by-type');
    register_block_type(__DIR__ . '/blocks/instructor-list');
    register_block_type(__DIR__ . '/blocks/studio-profile-card');
    register_block_type(__DIR__ . '/blocks/user-profile-card');
    register_block_type(__DIR__ . '/blocks/private-lesson-instructors');
    register_block_type(__DIR__ . '/blocks/package-list');
    register_block_type(__DIR__ . '/blocks/location-list');
    register_block_type(__DIR__ . '/blocks/event-schedule');
});

/** Admin + shortcodes */
add_action('plugins_loaded', function () {
    // Admin settings + Management API screens
    (new SettingsPage())->register();
    (new ManagementPage())->register();

    // "Sign in with BailaYa" (OpenID Connect)
    (new SignInWithBailaya())->register();

    // Shortcodes
    (new ClassSchedule())->register();
    (new ClassScheduleByType())->register();
    (new InstructorList())->register();
    (new StudioProfileCard())->register();
    (new UserProfileCard())->register();
    (new PrivateLessonInstructors())->register();
    (new PackageList())->register();
    (new LocationList())->register();
    (new EventSchedule())->register();
});
