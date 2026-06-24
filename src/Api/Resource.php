<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiClient;

use function array_map;
use function implode;
use function rawurlencode;

/**
 * Base class for typed, tag-focused API resources.
 *
 * Each concrete resource owns the endpoint paths for one WP Cloud API tag
 * (Sites, Backups, SSH, …) and exposes one semantic method per endpoint, so
 * callers never hand-build path strings. Path segments are individually
 * URL-encoded here — {identifier}/{domain} params are frequently real domains
 * and must be escaped.
 */
abstract class Resource
{
    public function __construct(protected readonly ApiClient $api)
    {
    }

    /**
     * Build a request path from raw segments, URL-encoding each one.
     */
    protected function path(string ...$segments): string
    {
        return implode('/', array_map(rawurlencode(...), $segments));
    }

    /**
     * Render a boolean as the literal `true`/`false` path segment the API
     * expects for toggle endpoints (e.g. /ssl-android-compat/{domain}/{enable}).
     */
    protected function flag(bool $value): string
    {
        return $value ? 'true' : 'false';
    }
}
