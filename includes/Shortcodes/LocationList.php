<?php
declare(strict_types=1);

namespace BailaYaWP\Shortcodes;

use BailaYaWP\ClientFactory;
use BailaYaWP\Helpers;
use BailaYaWP\Renderer;

if (!defined('ABSPATH')) exit;

final class LocationList
{
    public function register(): void
    {
        add_shortcode('bailaya_locations', [$this, 'handle']);
    }

    /**
     * Usage:
     * [bailaya_locations]
     * [bailaya_locations override_id="studio-xyz" locale="es" hide_directions="true" cache_ttl="600"]
     */
    public function handle(array $atts): string
    {
        $atts = shortcode_atts([
            'override_id'        => null,
            'locale'             => null,
            'hide_primary_badge' => 'false',
            'hide_directions'    => 'false',
            'cache_ttl'          => null,
        ], $atts);

        $overrideId       = Helpers::sanitize_studio_id($atts['override_id'] ?? null);
        $locale           = sanitize_text_field($atts['locale'] ?? get_locale() ?: 'en');
        $hidePrimaryBadge = filter_var($atts['hide_primary_badge'], FILTER_VALIDATE_BOOLEAN);
        $hideDirections   = filter_var($atts['hide_directions'], FILTER_VALIDATE_BOOLEAN);
        $ttl              = (int)($atts['cache_ttl'] ?? Helpers::get_option('cache_ttl', 300));
        $ttl              = max(0, $ttl);

        $cacheKey = implode('|', [
            'bailaya_locations',
            'v1',
            'studio:' . ($overrideId ?: (Helpers::get_option('studio_id') ?: 'none')),
        ]);

        $locations = null;
        if ($ttl > 0) {
            $cached = get_transient($cacheKey);
            if (is_array($cached)) {
                $locations = $cached;
            }
        }

        if ($locations === null) {
            try {
                $client    = ClientFactory::make($overrideId);
                $locations = $client->getLocations($overrideId);

                if ($ttl > 0) {
                    set_transient($cacheKey, $locations, $ttl);
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
                return '<div class="bailaya-error">' . esc_html__('Unable to load locations.', 'bailaya') . '</div>';
            }
        }

        return Renderer::locationList($locations, [
            'locale'           => $locale,
            'hidePrimaryBadge' => $hidePrimaryBadge,
            'hideDirections'   => $hideDirections,
        ]);
    }
}
