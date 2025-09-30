<?php
declare(strict_types=1);

namespace BailaYaWP;

use BailaYa\Client;

if (!defined('ABSPATH')) exit;

final class ClientFactory
{
    public static function make(?string $overrideStudioId = null): Client
    {
        $baseUrl  = (string)Helpers::get_option('base_url', 'https://www.bailaya.com/api');
        $studioId = Helpers::sanitize_studio_id($overrideStudioId) ?? Helpers::sanitize_studio_id(Helpers::get_option('studio_id'));

        return new Client([
            'baseUrl' => $baseUrl,
            // Pass only if set; Client will also look at env BAILAYA_STUDIO_ID
            ...($studioId ? ['studioId' => $studioId] : []),
        ]);
    }
}
