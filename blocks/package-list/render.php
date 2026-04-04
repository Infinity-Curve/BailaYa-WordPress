<?php
if (!defined('ABSPATH')) exit;

use BailaYaWP\ClientFactory;
use BailaYaWP\Renderer;
use BailaYaWP\Helpers;

/** @var array $attributes */
$atts = wp_parse_args($attributes ?? [], [
    'override_id'   => '',
    'locale'        => '',
    'hide_validity' => false,
    'cache_ttl'     => 0,
    'buy_base_url'  => '',
]);

$overrideId   = Helpers::sanitize_studio_id($atts['override_id'] ?: null);
$locale       = sanitize_text_field($atts['locale'] ?: get_locale() ?: 'en');
$hideValidity = (bool)$atts['hide_validity'];
$ttlAtt       = is_numeric($atts['cache_ttl']) ? (int)$atts['cache_ttl'] : 0;
$ttl          = $ttlAtt > 0 ? $ttlAtt : (int)Helpers::get_option('cache_ttl', 300);
$ttl          = max(0, $ttl);
$buyBaseUrl   = sanitize_url($atts['buy_base_url'] ?: Helpers::get_option('buy_base_url', 'https://www.bailaya.com/packages/'));

$cacheKey = implode('|', [
    'bailaya_packages_block',
    'v1',
    'studio:' . ($overrideId ?: (Helpers::get_option('studio_id') ?: 'none')),
]);

$packages = null;
if ($ttl > 0) {
    $cached = get_transient($cacheKey);
    if (is_array($cached)) {
        $packages = $cached;
    }
}

if ($packages === null) {
    try {
        $client   = ClientFactory::make($overrideId);
        $packages = $client->getPackages($overrideId);
        if ($ttl > 0) {
            set_transient($cacheKey, $packages, $ttl);
        }
    } catch (\Throwable $e) {
        if (current_user_can('manage_options')) {
            return '<div class="bailaya-error">BailaYa error: ' . esc_html($e->getMessage()) . '</div>';
        }
        return '<div class="bailaya-error">Unable to load packages.</div>';
    }
}

echo Renderer::packageList($packages, [
    'locale'       => $locale,
    'hideValidity' => $hideValidity,
    'buyBaseUrl'   => $buyBaseUrl,
]);
