<?php
declare(strict_types=1);

namespace BailaYaWP\Shortcodes;

use BailaYaWP\ClientFactory;
use BailaYaWP\Renderer;

if (!defined('ABSPATH')) exit;

final class UserProfileCard
{
    public function register(): void
    {
        add_shortcode('bailaya_user_profile', [$this, 'handle']);
    }

    /**
     * [bailaya_user_profile user_id="user-456" locale="es" cache_ttl="600" occupation_label="Ocupación" experience_label="Años"]
     */
    public function handle(array $atts): string
    {
        $atts = shortcode_atts([
            'user_id' => '',
            'locale' => '',
            'cache_ttl' => null,
            'occupation_label' => '',
            'experience_label' => '',
        ], $atts);

        $userId = is_string($atts['user_id']) ? trim($atts['user_id']) : '';
        if ($userId === '') {
            return '<div class="bailaya-error">Missing required attribute: user_id</div>';
        }

        $locale = is_string($atts['locale']) ? trim($atts['locale']) : '';

        // For user profiles, caching per-user is typically fine
        $ttl = max(0, (int)($atts['cache_ttl'] ?? 300));
        $cacheKey = 'bailaya_user_profile|v1|user:' . $userId;

        $profile = null;
        if ($ttl > 0) {
            $cached = get_transient($cacheKey);
            if (is_array($cached) || is_object($cached)) {
                $profile = $cached;
            }
        }

        if ($profile === null) {
            try {
                $client = ClientFactory::make(null);
                $profile = $client->getUserProfile($userId);
                if ($ttl > 0) {
                    set_transient($cacheKey, $profile, $ttl);
                }
            } catch (\Throwable $e) {
                if (current_user_can('manage_options')) {
                    return '<div class="bailaya-error">BailaYa error: ' . esc_html($e->getMessage()) . '</div>';
                }
                return '<div class="bailaya-error">Unable to load user profile.</div>';
            }
        }

        return Renderer::userProfileCard($profile, [
            'locale' => $locale ?: 'en',
            'labels' => [
                'occupationLabel' => (string)($atts['occupation_label'] ?? ''),
                'experienceLabel' => (string)($atts['experience_label'] ?? ''),
            ],
        ]);
    }
}
