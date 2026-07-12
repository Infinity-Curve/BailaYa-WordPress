<?php
if (!defined('ABSPATH')) exit;
// This file is included from inside a function (the block's render_callback), so the variables
// below are function-scoped, not global. PHPCS analyses it standalone and cannot
// see that, hence the disable.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

use BailaYaWP\ClientFactory;
use BailaYaWP\Renderer;
use BailaYaWP\Helpers;

/** @var array $attributes */
$atts = wp_parse_args($attributes ?? [], [
    'override_id'   => '',
    'locale'        => '',
    'from'          => '',
    'hide_location' => false,
    'cache_ttl'     => 0,
]);

$overrideId   = Helpers::sanitize_studio_id($atts['override_id'] ?: null);
$locale       = sanitize_text_field($atts['locale'] ?: get_locale() ?: 'en');
$fromStr      = Helpers::sanitize_date_yyyy_mm_dd($atts['from'] ?: null);
$hideLocation = (bool)$atts['hide_location'];
$ttlAtt       = is_numeric($atts['cache_ttl']) ? (int)$atts['cache_ttl'] : 0;
$ttl          = $ttlAtt > 0 ? $ttlAtt : (int)Helpers::get_option('cache_ttl', 300);
$ttl          = max(0, $ttl);

$cacheKey = implode('|', [
    'bailaya_events_block',
    'v1',
    'studio:' . ($overrideId ?: (Helpers::get_option('studio_id') ?: 'none')),
    'from:' . ($fromStr ?: 'today'),
]);

$events = null;
if ($ttl > 0) {
    $cached = get_transient($cacheKey);
    if (is_array($cached)) {
        $events = $cached;
    }
}

if ($events === null) {
    try {
        $client = ClientFactory::make($overrideId);
        $from   = $fromStr
            ? new DateTimeImmutable($fromStr, new DateTimeZone('UTC'))
            : null;

        $events = $client->getEvents($from, $overrideId);
        if ($ttl > 0) {
            set_transient($cacheKey, $events, $ttl);
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
        echo wp_kses('<div class="bailaya-error">' . esc_html__('Unable to load events.', 'bailaya') . '</div>', Helpers::allowed_html());
        return;
    }
}

echo wp_kses(
    Renderer::eventSchedule($events, [
    'locale'       => $locale,
    'hideLocation' => $hideLocation,
]),
    Helpers::allowed_html()
);
