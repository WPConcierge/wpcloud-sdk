<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Servers tag — datacenter and PHP-version capability lookups.
 *
 * @see https://wp.cloud/docs/api/tags/servers.md
 */
final class Servers extends Resource
{
    /**
     * List datacenters with available (non-full, active) servers for a client.
     *
     * GET /get-available-datacenters/{client}
     */
    public function getAvailableDatacenters(string $client): ApiResponse
    {
        return $this->api->get($this->path('get-available-datacenters', $client));
    }

    /**
     * List available PHP versions for a client. Pass `$verbose = true` for the
     * detailed (per-version metadata) variant.
     *
     * GET /get-php-versions/{client}[/verbose]
     */
    public function getPhpVersions(string $client, bool $verbose = false): ApiResponse
    {
        $segments = ['get-php-versions', $client];

        if ($verbose) {
            $segments[] = 'verbose';
        }

        return $this->api->get($this->path(...$segments));
    }
}
