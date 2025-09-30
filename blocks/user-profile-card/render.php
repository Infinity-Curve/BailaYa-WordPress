<?php
if (!defined('ABSPATH')) exit;

use BailaYaWP\ClientFactory;
use BailaYaWP\Renderer;

/** @var array $attributes */
$atts = wp_parse_args($attributes ?? [], [
    'user_id' => '',
    'locale' => '',
    'cache_ttl' => 0,
    'occupation_label' => '',
    'experience_label' => '',
]);

$userId = is_string($atts['user_id']) ? trim($atts['user_id']) : '';
if ($userId === '') {
    return '<div class="bailaya-error">Missing required attribute: user_id</div>';
}

$locale = is_string($atts['locale']) ? trim($atts['locale']) : '';

$ttl    = max(0, (int)($atts['cache_ttl'] ?? 300));
$cacheKey = 'bailaya_user_profile_block|v1|user:' . $userId;

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

echo Renderer::userProfileCard($profile, [
    'locale' => $locale ?: 'en',
    'labels' => [
        'occupationLabel' => (string)($atts['occupation_label'] ?? ''),
        'experienceLabel' => (string)($atts['experience_label'] ?? ''),
    ],
]);
