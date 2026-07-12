<?php
declare(strict_types=1);

namespace BailaYaWP;

use BailaYa\Dto\StudioLocation;

if (!defined('ABSPATH')) exit;

final class Helpers
{
    /**
     * Tags and attributes the front-end templates emit.
     *
     * Everything the templates output is already escaped at the point it is
     * written (esc_html / esc_attr / esc_url). Passing the finished markup
     * through wp_kses() with this list is a second gate: if a template ever
     * forgets, the worst that reaches the page is inert text. It is also what
     * lets the block render callbacks `echo` the Renderer's return value without
     * tripping WordPress.Security.EscapeOutput.
     *
     * @return array<string, array<string, bool>>
     */
    public static function allowed_html(): array
    {
        $common = ['class' => true, 'id' => true];

        return [
            'div'    => $common,
            'p'      => $common,
            'span'   => $common,
            'strong' => $common,
            'ul'     => $common,
            'li'     => $common,
            'h2'     => $common,
            'h3'     => $common,
            'h4'     => $common,
            'a'      => $common + [
                'href'   => true,
                'target' => true,
                'rel'    => true,
            ],
            'img'    => $common + [
                'src'     => true,
                'alt'     => true,
                'loading' => true,
            ],
        ];
    }

    /**
     * The admin screens additionally render forms and list tables.
     *
     * Note there is no `onclick` here, or anywhere: the delete confirmation is
     * bound from assets/js/admin.js against a data attribute rather than an
     * inline handler.
     *
     * @return array<string, array<string, bool>>
     */
    public static function allowed_admin_html(): array
    {
        $common = ['class' => true, 'id' => true];

        // array_merge, not `+`: the union operator keeps the LEFT key, so the
        // wider `a` defined here would be silently discarded in favour of the
        // front-end one.
        return array_merge(self::allowed_html(), [
            'h1'       => $common,
            'hr'       => $common,
            'form'     => $common + ['method' => true, 'action' => true],
            'table'    => $common,
            'thead'    => $common,
            'tbody'    => $common,
            'tr'       => $common,
            'th'       => $common + ['scope' => true, 'colspan' => true],
            'td'       => $common + ['colspan' => true],
            'label'    => $common + ['for' => true],
            'input'    => $common + [
                'type'         => true,
                'name'         => true,
                'value'        => true,
                'step'         => true,
                'min'          => true,
                'checked'      => true,
                'required'     => true,
                'autocomplete' => true,
                'placeholder'  => true,
            ],
            'select'   => $common + ['name' => true, 'required' => true],
            'option'   => $common + ['value' => true, 'selected' => true],
            'textarea' => $common + ['name' => true, 'rows' => true, 'required' => true],
            'code'     => $common,

            // The delete link carries its confirmation text as data, not script.
            'a' => $common + [
                'href'         => true,
                'target'       => true,
                'rel'          => true,
                'data-confirm' => true,
            ],
        ]);
    }

    /**
     * Build a single-line, display-ready address from a studio location.
     * Returns '' when the location carries no address parts.
     */
    public static function format_studio_location(?StudioLocation $location): string
    {
        if (!$location) return '';

        $region = implode(' ', array_filter([$location->state, $location->postalCode]));

        return implode(', ', array_filter([
            $location->addressLine1,
            $location->addressLine2,
            $location->city,
            $region !== '' ? $region : null,
            $location->country,
        ]));
    }

    /**
     * Pick the studio's primary location, falling back to the first one.
     * The API already returns locations primary-first; this is defensive.
     *
     * @param list<StudioLocation> $locations
     */
    public static function primary_location(array $locations): ?StudioLocation
    {
        if (!$locations) return null;

        foreach ($locations as $location) {
            if ($location->isPrimary) return $location;
        }
        return $locations[0];
    }

    /** Google Maps link for a studio location, preferring coordinates. '' when unmappable. */
    public static function studio_location_maps_url(?StudioLocation $location): string
    {
        if (!$location) return '';

        if ($location->latitude !== null && $location->longitude !== null) {
            return 'https://www.google.com/maps/search/?api=1&query='
                . $location->latitude . ',' . $location->longitude;
        }

        $address = self::format_studio_location($location);
        return $address !== ''
            ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address)
            : '';
    }

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
