<?php
declare(strict_types=1);

namespace BailaYaWP\Shortcodes;

use BailaYaWP\ClientFactory;
use BailaYaWP\Helpers;
use BailaYaWP\Renderer;

if (!defined('ABSPATH')) exit;

final class PackageList
{
    public function register(): void
    {
        add_shortcode('bailaya_packages', [$this, 'handle']);
    }

    /**
     * Usage:
     * [bailaya_packages]
     * [bailaya_packages override_id="studio-xyz" locale="es" hide_validity="true" cache_ttl="600"]
     */
    public function handle(array $atts): string
    {
        $atts = shortcode_atts([
            'override_id'   => null,
            'locale'        => null,
            'hide_validity' => 'false',
            'cache_ttl'     => null,
            'buy_base_url'  => null,
        ], $atts);

        $overrideId   = Helpers::sanitize_studio_id($atts['override_id'] ?? null);
        $locale       = sanitize_text_field($atts['locale'] ?? get_locale() ?: 'en');
        $hideValidity = filter_var($atts['hide_validity'], FILTER_VALIDATE_BOOLEAN);
        $ttl          = (int)($atts['cache_ttl'] ?? Helpers::get_option('cache_ttl', 300));
        $ttl          = max(0, $ttl);
        $buyBaseUrl   = sanitize_url($atts['buy_base_url'] ?? Helpers::get_option('buy_base_url', 'https://www.bailaya.com/packages/'));

        $cacheKey = implode('|', [
            'bailaya_packages',
            'v1',
            'studio:' . ($overrideId ?: (Helpers::get_option('studio_id') ?: 'none')),
        ]);

        $packages = null;
        if ($ttl > 0) {
            $cached = get_transient($cacheKey);
            if (is_array($cached)) {
                $packages = $cached;
            }
        }

        if ($packages === null) {
            try {
                $client   = ClientFactory::make($overrideId);
                $packages = $client->getPackages($overrideId);

                if ($ttl > 0) {
                    set_transient($cacheKey, $packages, $ttl);
                }
            } catch (\Throwable $e) {
                if (current_user_can('manage_options')) {
                    return '<div class="bailaya-error">BailaYa error: ' . esc_html($e->getMessage()) . '</div>';
                }
                return '<div class="bailaya-error">Unable to load packages.</div>';
            }
        }

        return Renderer::packageList($packages, [
            'locale'       => $locale,
            'hideValidity' => $hideValidity,
            'buyBaseUrl'   => $buyBaseUrl,
        ]);
    }
}
