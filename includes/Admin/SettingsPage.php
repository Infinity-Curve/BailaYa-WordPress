<?php
declare(strict_types=1);

namespace BailaYaWP\Admin;

use BailaYaWP\Helpers;

if (!defined('ABSPATH')) exit;

final class SettingsPage
{
    public function register(): void
    {
        add_action('admin_menu', function () {
            add_options_page(
                'BailaYa Settings',
                'BailaYa',
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

            add_settings_section('bailaya_main', 'API Configuration', function () {
                echo '<p>Configure the BailaYa API connection used by shortcodes and widgets.</p>';
            }, 'bailaya-settings');

            add_settings_field('base_url', 'API Base URL', function () {
                $val = esc_attr((string)Helpers::get_option('base_url', 'https://www.bailaya.com/api'));
                echo "<input type='url' name='bailaya_settings[base_url]' value='{$val}' class='regular-text' />";
            }, 'bailaya-settings', 'bailaya_main');

            add_settings_field('studio_id', 'Default Studio ID', function () {
                $val = esc_attr((string)Helpers::get_option('studio_id', ''));
                echo "<input type='text' name='bailaya_settings[studio_id]' value='{$val}' class='regular-text' />";
                echo '<p class="description">Can be overridden per shortcode via <code>override_id</code>.</p>';
            }, 'bailaya-settings', 'bailaya_main');

            add_settings_field('cache_ttl', 'Cache TTL (seconds)', function () {
                $val = (int)Helpers::get_option('cache_ttl', 300);
                echo "<input type='number' name='bailaya_settings[cache_ttl]' value='{$val}' min='0' step='1' />";
                echo '<p class="description">0 disables caching.</p>';
            }, 'bailaya-settings', 'bailaya_main');
        });
    }

    /** @param array $input */
    public function sanitize(array $input): array
    {
        return [
            'base_url' => isset($input['base_url']) ? esc_url_raw($input['base_url']) : 'https://www.bailaya.com/api',
            'studio_id' => isset($input['studio_id']) ? sanitize_text_field($input['studio_id']) : '',
            'cache_ttl' => isset($input['cache_ttl']) ? max(0, (int)$input['cache_ttl']) : 300,
        ];
    }

    public function render(): void
    {
        ?>
        <div class="wrap">
            <h1>BailaYa Settings</h1>
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
