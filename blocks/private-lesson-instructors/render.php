<?php
if (!defined('ABSPATH')) exit;

use BailaYaWP\ClientFactory;
use BailaYaWP\Renderer;
use BailaYaWP\Helpers;

/** @var array $attributes */
$atts = wp_parse_args($attributes ?? [], [
    'override_id'   => '',
    'locale'        => '',
    'cache_ttl'     => 0,
    'book_base_url' => '',
]);

$overrideId  = Helpers::sanitize_studio_id($atts['override_id'] ?: null);
$locale      = sanitize_text_field($atts['locale'] ?: get_locale() ?: 'en');
$ttlAtt      = is_numeric($atts['cache_ttl']) ? (int)$atts['cache_ttl'] : 0;
$ttl         = $ttlAtt > 0 ? $ttlAtt : (int)Helpers::get_option('cache_ttl', 300);
$ttl         = max(0, $ttl);
$bookBaseUrl = sanitize_url($atts['book_base_url'] ?: Helpers::get_option('book_base_url', 'https://www.bailaya.com/en/book/private-lesson/'));

$cacheKey = implode('|', [
    'bailaya_pl_instructors_block',
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
        $client      = ClientFactory::make($overrideId);
        $instructors = $client->getPrivateLessonInstructors($overrideId);
        if ($ttl > 0) {
            set_transient($cacheKey, $instructors, $ttl);
        }
    } catch (\Throwable $e) {
        if (current_user_can('manage_options')) {
            return '<div class="bailaya-error">BailaYa error: ' . esc_html($e->getMessage()) . '</div>';
        }
        return '<div class="bailaya-error">Unable to load private lesson instructors.</div>';
    }
}

echo Renderer::privateLessonInstructors($instructors, [
    'locale'      => $locale,
    'bookBaseUrl' => $bookBaseUrl,
]);
