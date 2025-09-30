<?php
declare(strict_types=1);

namespace BailaYaWP\Shortcodes;

use BailaYaWP\ClientFactory;
use BailaYaWP\Helpers;
use BailaYaWP\Renderer;

if (!defined('ABSPATH')) exit;

final class StudioProfileCard
{
    public function register(): void
    {
        add_shortcode('bailaya_studio_profile', [$this, 'handle']);
    }

    /**
     * [bailaya_studio_profile override_id="studio-123" locale="es" cache_ttl="600" address_label="Dirección" business_hours_label="Horario"]
     */
    public function handle(array $atts): string
    {
        $atts = shortcode_atts([
            'override_id' => null,
            'locale' => null,
            'cache_ttl' => null,
            'address_label' => '',
            'business_hours_label' => '',
        ], $atts);

        $overrideId = Helpers::sanitize_studio_id($atts['override_id'] ?? null);
        $locale = is_string($atts['locale'] ?? null) ? trim($atts['locale']) : null;

        $ttl = (int)($atts['cache_ttl'] ?? Helpers::get_option('cache_ttl', 300));
        $ttl = max(0, $ttl);

        $cacheKey = implode('|', [
            'bailaya_studio_profile',
            'v1',
            'studio:' . ($overrideId ?: (Helpers::get_option('studio_id') ?: 'none')),
        ]);

        $profile = null;
        if ($ttl > 0) {
            $cached = get_transient($cacheKey);
            if (is_array($cached) || is_object($cached)) {
                $profile = $cached;
            }
        }

        if ($profile === null) {
            try {
                $client = ClientFactory::make($overrideId);
                $profile = $client->getStudioProfile($overrideId);
                if ($ttl > 0) {
                    set_transient($cacheKey, $profile, $ttl);
                }
            } catch (\Throwable $e) {
                if (current_user_can('manage_options')) {
                    return '<div class="bailaya-error">BailaYa error: ' . esc_html($e->getMessage()) . '</div>';
                }
                return '<div class="bailaya-error">Unable to load studio profile.</div>';
            }
        }

        return Renderer::studioProfileCard($profile, [
            'locale' => $locale ?: 'en',
            'labels' => [
                'addressLabel' => (string)($atts['address_label'] ?? ''),
                'businessHoursLabel' => (string)($atts['business_hours_label'] ?? ''),
            ],
        ]);
    }
}
