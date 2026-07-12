<?php
/**
 * Server-side render for bailaya/class-schedule
 *
 * @var array $attributes
 */

if (!defined('ABSPATH')) exit;
// This file is included from inside a function (the block's render_callback), so the variables
// below are function-scoped, not global. PHPCS analyses it standalone and cannot
// see that, hence the disable.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

use BailaYaWP\ClientFactory;
use BailaYaWP\Renderer;
use BailaYaWP\Helpers;

$atts = wp_parse_args($attributes ?? [], [
    'from' => '',
    'override_id' => '',
    'locale' => '',
    'cache_ttl' => 0,
]);

$fromStr     = Helpers::sanitize_date_yyyy_mm_dd($atts['from'] ?: null);
$overrideId  = Helpers::sanitize_studio_id($atts['override_id'] ?: null);
$locale      = is_string($atts['locale']) ? trim($atts['locale']) : '';
$cacheTtlAtt = is_numeric($atts['cache_ttl']) ? (int)$atts['cache_ttl'] : 0;

// Resolve TTL: block attribute overrides plugin default if >0, otherwise use setting
$ttl = $cacheTtlAtt > 0 ? $cacheTtlAtt : (int)Helpers::get_option('cache_ttl', 300);
$ttl = max(0, $ttl);

// Build cache key
$cacheKey = implode('|', [
    'bailaya_classes_block',
    'v1',
    'studio:' . ($overrideId ?: (Helpers::get_option('studio_id') ?: 'none')),
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

        $classes = $client->getClasses($from, $overrideId);
        if ($ttl > 0) {
            set_transient($cacheKey, $classes, $ttl);
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
        echo wp_kses('<div class="bailaya-error">' . esc_html__('Unable to load schedule.', 'bailaya') . '</div>', Helpers::allowed_html());
        return;
    }
}

echo wp_kses(
    Renderer::classSchedule($classes, [
    'locale' => $locale ?: null,
]),
    Helpers::allowed_html()
);
