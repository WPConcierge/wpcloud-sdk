<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Usage tag — time-series resource usage data for a site.
 *
 * @see https://wp.cloud/docs/api/tags/usage.md
 */
final class Usage extends Resource
{
    /**
     * Get time-series usage data for a site.
     *
     * POST /usage/{site}
     *
     * @param array<string, mixed> $body start, end, resolution, forecast.
     */
    public function get(string $site, array $body): ApiResponse
    {
        return $this->api->post($this->path('usage', $site), $body);
    }
}
