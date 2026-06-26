<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Support;

use function is_array;
use function is_scalar;
use function is_string;
use function json_decode;
use function json_encode;

/**
 * The versioned `_data` site meta that carries the billing site type.
 *
 * Schema: {"v1":{"site_type":"staging|internal|billable"}}. A site with no
 * site_type is billed by default, so we always set one. The API double-encodes
 * `_data` on read (quotes are backslash-escaped), which {@see self::decode()}
 * unwraps.
 */
final class SiteData
{
    public const VERSION = 'v1';

    /** Client-settable site types (canary is reserved for the WP Cloud team). */
    public const SITE_TYPES = ['staging', 'internal', 'billable'];

    /** Encode a fresh `_data` payload for the given site type. */
    public static function encode(string $siteType): string
    {
        return (string) json_encode([self::VERSION => ['site_type' => $siteType]]);
    }

    /**
     * Decode a `_data` meta value into an array. A direct decode is tried first;
     * if it fails (the value is double-encoded) one wrapping layer is removed by
     * decoding it as a JSON string. Returns [] when the value is unusable.
     *
     * @return array<array-key, mixed>
     */
    public static function decode(string $raw): array
    {
        $decoded = json_decode($raw, true);
        if ($decoded === null) {
            $unwrapped = json_decode('"' . $raw . '"');
            $decoded   = is_string($unwrapped) ? json_decode($unwrapped, true) : null;
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Read the site_type out of decoded `_data`, or '' if none is set.
     *
     * @param  array<array-key, mixed>  $data
     */
    public static function siteType(array $data): string
    {
        foreach ($data as $version) {
            if (is_array($version) && isset($version['site_type']) && is_scalar($version['site_type'])) {
                return (string) $version['site_type'];
            }
        }

        return '';
    }

    /**
     * Merge a site type into existing decoded `_data` (preserving other keys)
     * and return the JSON to write back.
     *
     * @param  array<array-key, mixed>  $data
     */
    public static function withSiteType(array $data, string $siteType): string
    {
        if (! isset($data[self::VERSION]) || ! is_array($data[self::VERSION])) {
            $data[self::VERSION] = [];
        }

        $data[self::VERSION]['site_type'] = $siteType;

        return (string) json_encode($data);
    }
}
