<?php
if (!defined('ABSPATH')) exit;
// This file is included from inside a function (the block's render_callback), so the variables
// below are function-scoped, not global. PHPCS analyses it standalone and cannot
// see that, hence the disable.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

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
    echo wp_kses('<div class="bailaya-error">' . esc_html__('Missing required attribute: user_id', 'bailaya') . '</div>', Helpers::allowed_html());
    return;
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
            echo wp_kses('<div class="bailaya-error">'
                    . esc_html(sprintf(
                        /* translators: %s: error message from the BailaYa API */
                        __('BailaYa error: %s', 'bailaya'),
                        $e->getMessage()
                    ))
                    . '</div>', Helpers::allowed_html());
            return;
        }
        echo wp_kses('<div class="bailaya-error">' . esc_html__('Unable to load user profile.', 'bailaya') . '</div>', Helpers::allowed_html());
        return;
    }
}

echo wp_kses(
    Renderer::userProfileCard($profile, [
    'locale' => $locale ?: 'en',
    'labels' => [
        'occupationLabel' => (string)($atts['occupation_label'] ?? ''),
        'experienceLabel' => (string)($atts['experience_label'] ?? ''),
    ],
]),
    Helpers::allowed_html()
);
