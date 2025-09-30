<?php
if (!defined('ABSPATH')) exit;

use BailaYaWP\ClientFactory;
use BailaYaWP\Renderer;
use BailaYaWP\Helpers;

/** @var array $attributes */
$atts = wp_parse_args($attributes ?? [], [
    'override_id' => '',
    'locale' => '',
    'cache_ttl' => 0,
    'address_label' => '',
    'business_hours_label' => '',
]);

$overrideId = Helpers::sanitize_studio_id($atts['override_id'] ?: null);
$locale     = is_string($atts['locale']) ? trim($atts['locale']) : '';

$ttlAtt = is_numeric($atts['cache_ttl']) ? (int)$atts['cache_ttl'] : 0;
$ttl    = $ttlAtt > 0 ? $ttlAtt : (int)Helpers::get_option('cache_ttl', 300);
$ttl    = max(0, $ttl);

$cacheKey = implode('|', [
    'bailaya_studio_profile_block',
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

echo Renderer::studioProfileCard($profile, [
    'locale' => $locale ?: 'en',
    'labels' => [
        'addressLabel' => (string)($atts['address_label'] ?? ''),
        'businessHoursLabel' => (string)($atts['business_hours_label'] ?? ''),
    ],
]);
