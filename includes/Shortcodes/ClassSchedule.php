<?php
declare(strict_types=1);

namespace BailaYaWP\Shortcodes;

use BailaYaWP\ClientFactory;
use BailaYaWP\Helpers;
use BailaYaWP\Renderer;
use DateTimeImmutable;
use DateTimeZone;

if (!defined('ABSPATH')) exit;

final class ClassSchedule
{
    public function register(): void
    {
        add_shortcode('bailaya_class_schedule', [$this, 'handle']);
    }

    /**
     * [bailaya_class_schedule from="2025-10-01" override_id="studio-xyz" locale="en"]
     */
    public function handle(array $atts, ?string $content = null, string $tag = ''): string
    {
        $atts = shortcode_atts([
            'from' => null,           // YYYY-MM-DD
            'override_id' => null,    // optional studio
            'locale' => null,
            'cache_ttl' => null,      // override cache in this instance
        ], $atts, $tag);

        $fromStr = Helpers::sanitize_date_yyyy_mm_dd($atts['from'] ?? null);
        $overrideId = Helpers::sanitize_studio_id($atts['override_id'] ?? null);
        $locale = is_string($atts['locale'] ?? null) ? trim($atts['locale']) : null;

        // Resolve cache ttl
        $ttl = (int)($atts['cache_ttl'] ?? Helpers::get_option('cache_ttl', 300));
        $ttl = max(0, $ttl);

        // Build cache key
        $cacheKeyParts = [
            'bailaya_classes',
            'v1',
            'studio:' . ($overrideId ?: (Helpers::get_option('studio_id') ?: 'none')),
            'from:' . ($fromStr ?: 'today'),
        ];
        $cacheKey = implode('|', $cacheKeyParts);

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
                $from = $fromStr
                    ? new DateTimeImmutable($fromStr, new DateTimeZone('UTC'))
                    : null;

                $classes = $client->getClasses($from, $overrideId);
                if ($ttl > 0) {
                    set_transient($cacheKey, $classes, $ttl);
                }
            } catch (\Throwable $e) {
                // Show a friendly error to editors; hide details to visitors
                if (current_user_can('manage_options')) {
                    return '<div class="bailaya-error">BailaYa error: ' . esc_html($e->getMessage()) . '</div>';
                }
                return '<div class="bailaya-error">Unable to load schedule.</div>';
            }
        }

        return Renderer::classSchedule($classes, [
            'locale' => $locale ?: null,
        ]);
    }
}
