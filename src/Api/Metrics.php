<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Metrics tag — time-series metrics for clients and sites.
 *
 * Pass `$summarize = true` to receive aggregated totals over the window instead
 * of bucketed time series (the `/summarize` path variant).
 *
 * @see https://wp.cloud/docs/api/tags/metrics.md
 */
final class Metrics extends Resource
{
    /**
     * Get time-series metrics for a client or site key.
     *
     * POST /metrics/{type}/{key}[/summarize]
     *
     * @param string               $type One of: client, site.
     * @param array<string, mixed> $body start, end, metric, dimension,
     *                                    resolution, max_bucket_size, filters.
     */
    public function get(string $type, string $key, array $body, bool $summarize = false): ApiResponse
    {
        $segments = ['metrics', $type, $key];

        if ($summarize) {
            $segments[] = 'summarize';
        }

        return $this->api->post($this->path(...$segments), $body);
    }

    /**
     * Get time-series metrics for a single site.
     *
     * POST /site-metrics/{site}[/summarize]
     *
     * @param array<string, mixed> $body start, end, metric, dimension,
     *                                    resolution, max_bucket_size, filters.
     */
    public function site(string $site, array $body, bool $summarize = false): ApiResponse
    {
        $segments = ['site-metrics', $site];

        if ($summarize) {
            $segments[] = 'summarize';
        }

        return $this->api->post($this->path(...$segments), $body);
    }
}
