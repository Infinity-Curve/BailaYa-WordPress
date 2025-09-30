<?php
declare(strict_types=1);

namespace BailaYaWP\Shortcodes;

use BailaYaWP\ClientFactory;
use BailaYaWP\Helpers;
use BailaYaWP\Renderer;
use DateTimeImmutable;
use DateTimeZone;

if (!defined('ABSPATH')) exit;

final class ClassScheduleByType
{
    public function register(): void
    {
        add_shortcode('bailaya_class_schedule_by_type', [$this, 'handle']);
    }

    /**
     * Usage:
     * [bailaya_class_schedule_by_type type="salsa" from="2025-10-01" override_id="studio-xyz" locale="en" cache_ttl="600"]
     */
    public function handle(array $atts): string
    {
        $atts = shortcode_atts([
            'type'        => '',       // required
            'from'        => null,     // YYYY-MM-DD
            'override_id' => null,
            'locale'      => null,
            'cache_ttl'   => null,
        ], $atts);

        $typeName   = is_string($atts['type']) ? trim($atts['type']) : '';
        if ($typeName === '') {
            return '<div class="bailaya-error">Missing required attribute: type</div>';
        }

        $fromStr    = Helpers::sanitize_date_yyyy_mm_dd($atts['from'] ?? null);
        $overrideId = Helpers::sanitize_studio_id($atts['override_id'] ?? null);
        $locale     = is_string($atts['locale'] ?? null) ? trim($atts['locale']) : null;

        $ttl = (int)($atts['cache_ttl'] ?? Helpers::get_option('cache_ttl', 300));
        $ttl = max(0, $ttl);

        $cacheKey = implode('|', [
            'bailaya_classes_by_type',
            'v1',
            'studio:' . ($overrideId ?: (Helpers::get_option('studio_id') ?: 'none')),
            'type:' . strtolower($typeName),
            'from:' . ($fromStr ?: 'today'),
        ]);

        $classes = null;
        if ($ttl > 0) {
            $cached = get_transient($cacheKey);
            if (is_array($cached)) {
                $classes = $cached;
            }
        }

        if ($classes === null) {
            try {
                $client = ClientFactory::make($overrideId);
                $from = $fromStr ? new DateTimeImmutable($fromStr, new DateTimeZone('UTC')) : null;

                $classes = $client->getClassesByType($typeName, $from, $overrideId);
                if ($ttl > 0) {
                    set_transient($cacheKey, $classes, $ttl);
                }
            } catch (\Throwable $e) {
                if (current_user_can('manage_options')) {
                    return '<div class="bailaya-error">BailaYa error: ' . esc_html($e->getMessage()) . '</div>';
                }
                return '<div class="bailaya-error">Unable to load schedule.</div>';
            }
        }

        // Reuse the same list template & CSS classes used by the non-filtered schedule
        return Renderer::classSchedule($classes, [
            'locale' => $locale ?: null,
        ]);
    }
}
