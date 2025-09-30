<?php
/**
 * Plugin Name: BailaYa
 * Description: Embed BailaYa schedules, instructors and profiles in WordPress via shortcodes and widgets.
 * Version: 1.0.0
 * Author: BailaYa
 * Requires PHP: 8.1
 * License: ISC
 */

if (!defined('ABSPATH')) exit;

define('BAILAYA_WP_VER', '0.1.0');
define('BAILAYA_WP_PATH', plugin_dir_path(__FILE__));
define('BAILAYA_WP_URL', plugin_dir_url(__FILE__));

/** Composer autoload (prefer plugin vendor/ then site vendor/) */
$autoloads = [
    BAILAYA_WP_PATH . 'vendor/autoload.php',
    WP_CONTENT_DIR . '/vendor/autoload.php',
];
foreach ($autoloads as $autoload) {
    if (is_readable($autoload)) {
        require_once $autoload;
        break;
    }
}

use BailaYaWP\Admin\SettingsPage;
use BailaYaWP\Shortcodes\ClassSchedule;
use BailaYaWP\Shortcodes\ClassScheduleByType;
use BailaYaWP\Shortcodes\InstructorList;
use BailaYaWP\Shortcodes\StudioProfileCard;

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
});

/** Admin + shortcodes */
add_action('plugins_loaded', function () {
    // Admin settings
    (new SettingsPage())->register();

    // Shortcodes
    (new ClassSchedule())->register();
    (new ClassScheduleByType())->register();
    (new InstructorList())->register();
    (new StudioProfileCard())->register();
    (new UserProfileCard())->register();
});
