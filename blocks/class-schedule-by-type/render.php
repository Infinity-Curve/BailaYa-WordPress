<?php
if (!defined('ABSPATH')) exit;

use BailaYaWP\ClientFactory;
use BailaYaWP\Renderer;
use BailaYaWP\Helpers;
use DateTimeImmutable;
use DateTimeZone;

/** @var array $attributes */
$atts = wp_parse_args($attributes ?? [], [
    'type_name'   => '',
    'from'        => '',
    'override_id' => '',
    'locale'      => '',
    'cache_ttl'   => 0,
]);

$typeName   = is_string($atts['type_name']) ? trim($atts['type_name']) : '';
if ($typeName === '') {
    return '<div class="bailaya-error">Missing required attribute: type_name</div>';
}

$fromStr    = Helpers::sanitize_date_yyyy_mm_dd($atts['from'] ?: null);
$overrideId = Helpers::sanitize_studio_id($atts['override_id'] ?: null);
$locale     = is_string($atts['locale']) ? trim($atts['locale']) : '';
$ttlAtt     = is_numeric($atts['cache_ttl']) ? (int)$atts['cache_ttl'] : 0;
$ttl        = $ttlAtt > 0 ? $ttlAtt : (int)Helpers::get_option('cache_ttl', 300);
$ttl        = max(0, $ttl);

$cacheKey = implode('|', [
    'bailaya_classes_by_type_block',
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

echo Renderer::classSchedule($classes, [
    'locale' => $locale ?: null,
]);
