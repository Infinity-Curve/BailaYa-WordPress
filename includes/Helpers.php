<?php
declare(strict_types=1);

namespace BailaYaWP;

if (!defined('ABSPATH')) exit;

final class Helpers
{
    public static function get_option(string $key, mixed $default = null): mixed
    {
        $opts = get_option('bailaya_settings', []);
        return $opts[$key] ?? $default;
    }

    public static function sanitize_studio_id(?string $v): ?string
    {
        $v = is_string($v) ? trim($v) : null;
        return $v !== '' ? $v : null;
    }

    public static function sanitize_date_yyyy_mm_dd(?string $v): ?string
    {
        if (!$v) return null;
        $v = trim($v);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null;
    }

    public static function esc_text(?string $v): string
    {
        return esc_html($v ?? '');
    }
}
