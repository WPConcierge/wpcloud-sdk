<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Domains tag — domain preflight and hosting eligibility checks.
 *
 * Wraps the check-can-host-domain endpoint as a dedicated resource accessor
 * so callers can reach it via `$api->domains()->checkCanHost(...)`.
 *
 * @see https://wp.cloud/docs/api/tags/sites.md
 */
final class Domains extends Resource
{
    /**
     * Check whether a client can host a domain on WP Cloud. Intended as a
     * preflight before site:create. `data.allowed` is the boolean verdict;
     * it is false for domains already hosted on WordPress.com / WP Cloud.
     *
     * GET /check-can-host-domain/{client}/{domain}
     */
    public function checkCanHost(string $client, string $domain): ApiResponse
    {
        return $this->api->get($this->path('check-can-host-domain', $client, $domain));
    }
}
