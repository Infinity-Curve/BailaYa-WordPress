<?php
declare(strict_types=1);

namespace BailaYaWP\Shortcodes;

use BailaYaWP\ClientFactory;
use BailaYaWP\Helpers;
use BailaYaWP\Renderer;
use DateTimeImmutable;
use DateTimeZone;

if (!defined('ABSPATH')) exit;

final class EventSchedule
{
    public function register(): void
    {
        add_shortcode('bailaya_events', [$this, 'handle']);
    }

    /**
     * [bailaya_events from="2026-10-01" override_id="studio-xyz" locale="en"]
     */
    public function handle(array $atts, ?string $content = null, string $tag = ''): string
    {
        $atts = shortcode_atts([
            'from' => null,           // YYYY-MM-DD
            'override_id' => null,
            'locale' => null,
            'cache_ttl' => null,
            'hide_location' => 'false',
        ], $atts, $tag);

        $fromStr = Helpers::sanitize_date_yyyy_mm_dd($atts['from'] ?? null);
        $overrideId = Helpers::sanitize_studio_id($atts['override_id'] ?? null);
        $locale = is_string($atts['locale'] ?? null) ? trim($atts['locale']) : null;
        $hideLocation = filter_var($atts['hide_location'], FILTER_VALIDATE_BOOLEAN);

        $ttl = (int)($atts['cache_ttl'] ?? Helpers::get_option('cache_ttl', 300));
        $ttl = max(0, $ttl);

        $cacheKey = implode('|', [
            'bailaya_events',
            'v1',
            'studio:' . ($overrideId ?: (Helpers::get_option('studio_id') ?: 'none')),
            'from:' . ($fromStr ?: 'today'),
        ]);

        $events = null;
        if ($ttl > 0) {
            $cached = get_transient($cacheKey);
            if (is_array($cached)) {
                $events = $cached;
            }
        }

        if ($events === null) {
            try {
                $client = ClientFactory::make($overrideId);
                $from = $fromStr
                    ? new DateTimeImmutable($fromStr, new DateTimeZone('UTC'))
                    : null;

                $events = $client->getEvents($from, $overrideId);
                if ($ttl > 0) {
                    set_transient($cacheKey, $events, $ttl);
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
                return '<div class="bailaya-error">' . esc_html__('Unable to load events.', 'bailaya') . '</div>';
            }
        }

        return Renderer::eventSchedule($events, [
            'locale' => $locale ?: null,
            'hideLocation' => $hideLocation,
        ]);
    }
}
