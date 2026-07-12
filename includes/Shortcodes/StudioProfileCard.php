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
     *
     * By default the card shows the studio's primary location. Pass
     * show_all_locations="true" to list every location instead.
     */
    public function handle(array $atts): string
    {
        $atts = shortcode_atts([
            'override_id' => null,
            'locale' => null,
            'cache_ttl' => null,
            'address_label' => '',
            'business_hours_label' => '',
            'show_all_locations' => 'false',
        ], $atts);

        $overrideId = Helpers::sanitize_studio_id($atts['override_id'] ?? null);
        $locale = is_string($atts['locale'] ?? null) ? trim($atts['locale']) : null;
        $showAllLocations = filter_var($atts['show_all_locations'], FILTER_VALIDATE_BOOLEAN);

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
                    return '<div class="bailaya-error">'
                    . esc_html(sprintf(
                        /* translators: %s: error message from the BailaYa API */
                        __('BailaYa error: %s', 'bailaya'),
                        $e->getMessage()
                    ))
                    . '</div>';
                }
                return '<div class="bailaya-error">' . esc_html__('Unable to load studio profile.', 'bailaya') . '</div>';
            }
        }

        return Renderer::studioProfileCard($profile, [
            'locale' => $locale ?: 'en',
            'showAllLocations' => $showAllLocations,
            'labels' => [
                'addressLabel' => (string)($atts['address_label'] ?? ''),
                'businessHoursLabel' => (string)($atts['business_hours_label'] ?? ''),
            ],
        ]);
    }
}
