<?php
declare(strict_types=1);

namespace BailaYaWP;

use BailaYa\Client;
use BailaYa\OAuth;

if (!defined('ABSPATH')) exit;

final class ClientFactory
{
    /**
     * Builds a client for the public API, and for the authenticated Management
     * API (`/v1`) when an API key or access token is configured in settings.
     */
    public static function make(?string $overrideStudioId = null): Client
    {
        $baseUrl  = (string)Helpers::get_option('base_url', 'https://www.bailaya.com/api');
        $studioId = Helpers::sanitize_studio_id($overrideStudioId) ?? Helpers::sanitize_studio_id(Helpers::get_option('studio_id'));

        // Management API credentials. Optional — the public endpoints need none.
        $apiKey      = Helpers::sanitize_studio_id(Helpers::get_option('api_key'));
        $accessToken = Helpers::sanitize_studio_id(Helpers::get_option('access_token'));

        return new Client([
            'baseUrl' => $baseUrl,
            // Pass only if set; Client also looks at env BAILAYA_STUDIO_ID,
            // BAILAYA_API_KEY and BAILAYA_API_TOKEN.
            ...($studioId ? ['studioId' => $studioId] : []),
            ...($apiKey ? ['apiKey' => $apiKey] : []),
            ...($accessToken ? ['accessToken' => $accessToken] : []),
        ]);
    }

    /**
     * OAuth 2.0 / OpenID Connect helpers ("Sign in with BailaYa"), scoped to the
     * configured base URL (issuer `${baseUrl}/oidc`).
     */
    public static function oauth(): OAuth
    {
        return self::make()->oauth();
    }
}
