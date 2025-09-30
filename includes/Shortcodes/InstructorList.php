<?php
declare(strict_types=1);

namespace BailaYaWP\Shortcodes;

use BailaYaWP\ClientFactory;
use BailaYaWP\Helpers;
use BailaYaWP\Renderer;

if (!defined('ABSPATH')) exit;

final class InstructorList
{
    public function register(): void
    {
        add_shortcode('bailaya_instructors', [$this, 'handle']);
    }

    /**
     * Usage:
     * [bailaya_instructors]
     * [bailaya_instructors override_id="studio-xyz" cache_ttl="600"]
     */
    public function handle(array $atts): string
    {
        $atts = shortcode_atts([
            'override_id' => null,
            'cache_ttl'   => null,
        ], $atts);

        $overrideId = Helpers::sanitize_studio_id($atts['override_id'] ?? null);
        $ttl = (int)($atts['cache_ttl'] ?? Helpers::get_option('cache_ttl', 300));
        $ttl = max(0, $ttl);

        $cacheKey = implode('|', [
            'bailaya_instructors',
            'v1',
            'studio:' . ($overrideId ?: (Helpers::get_option('studio_id') ?: 'none')),
        ]);

        $instructors = null;
        if ($ttl > 0) {
            $cached = get_transient($cacheKey);
            if (is_array($cached)) {
                $instructors = $cached;
            }
        }

        if ($instructors === null) {
            try {
                $client = ClientFactory::make($overrideId);
                $instructors = $client->getInstructors($overrideId);

                if ($ttl > 0) {
                    set_transient($cacheKey, $instructors, $ttl);
                }
            } catch (\Throwable $e) {
                if (current_user_can('manage_options')) {
                    return '<div class="bailaya-error">BailaYa error: ' . esc_html($e->getMessage()) . '</div>';
                }
                return '<div class="bailaya-error">Unable to load instructors.</div>';
            }
        }

        return Renderer::instructorList($instructors);
    }
}
