<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Logs tag — query a site's web server and PHP error logs.
 *
 * All endpoints accept a time window (`start`/`end`) plus paging and filtering
 * options in the request body.
 *
 * @see https://wp.cloud/docs/api/tags/logs.md
 */
final class Logs extends Resource
{
    /**
     * Get web server logs for a site (client-scoped variant).
     *
     * POST /logs/site/{site}?client={client}
     *
     * @param array<string, mixed> $body start, end, page_size, scroll_id,
     *                                    sort_order, filter, resolution, range.
     */
    public function site(string $site, string $client, array $body): ApiResponse
    {
        return $this->api->post($this->path('logs', 'site', $site), $body, ['client' => $client]);
    }

    /**
     * Get a site's PHP error logs.
     *
     * POST /site-error-logs/{site}
     *
     * @param array<string, mixed> $body start, end, page_size, scroll_id,
     *                                    sort_order, filter.
     */
    public function errors(string $site, array $body): ApiResponse
    {
        return $this->api->post($this->path('site-error-logs', $site), $body);
    }

    /**
     * Get a site's web server logs.
     *
     * POST /site-logs/{site}
     *
     * @param array<string, mixed> $body start, end, page_size, scroll_id,
     *                                    sort_order, filter.
     */
    public function webServer(string $site, array $body): ApiResponse
    {
        return $this->api->post($this->path('site-logs', $site), $body);
    }
}
