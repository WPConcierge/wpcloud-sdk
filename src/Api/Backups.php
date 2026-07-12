<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;
use WPConcierge\WPCloud\Http\BinaryStream;

/**
 * Backups tag — on-demand backups and backup inspection/restore data.
 *
 * Sites are addressed either by a bare `{site}` (ID or domain) or by a
 * `{service}/{identifier}` pair, depending on the endpoint.
 *
 * @see https://wp.cloud/docs/api/tags/backups.md
 */
final class Backups extends Resource
{
    /**
     * Request an on-demand backup (async → returns job_id).
     *
     * POST /on-demand-backup/create/{site}/{type}
     *
     * @param string $type Backup type: "fs" (filesystem) or "db" (database).
     */
    public function create(string $site, string $type): ApiResponse
    {
        return $this->api->post($this->path('on-demand-backup', 'create', $site, $type));
    }

    /**
     * Request deletion of an on-demand backup (async → returns job_id).
     *
     * POST /on-demand-backup/delete/{site}/{backup_id}
     */
    public function delete(string $site, string $backupId): ApiResponse
    {
        return $this->api->post($this->path('on-demand-backup', 'delete', $site, $backupId));
    }

    /**
     * Download a backup's raw bytes (db = gzipped SQL, fs = bzip2 tar of ./htdocs).
     *
     * GET /site-backup-get/{service}/{identifier}/{backup_id}
     *
     * The body is streamed, not buffered — real backups run to hundreds of MB.
     */
    public function download(string $service, string $identifier, string $backupId): BinaryStream
    {
        return $this->api->download($this->path('site-backup-get', $service, $identifier, $backupId));
    }

    /**
     * Get the data payload for a single backup.
     *
     * GET /site-backup-get/{service}/{identifier}/{backup_id}
     *
     * @deprecated Broken by construction: this endpoint returns raw binary, but
     *             the JSON path buffers the whole body and json_decode()s it, so
     *             every call throws ApiException('Unparseable response'). Use
     *             {@see download} instead. Kept only to avoid a breaking change;
     *             remove on the next major.
     */
    public function get(string $service, string $identifier, string $backupId): ApiResponse
    {
        return $this->api->get($this->path('site-backup-get', $service, $identifier, $backupId));
    }

    /**
     * Get metadata/details about a single backup.
     *
     * GET /site-backup-info/{service}/{identifier}/{backup_id}
     */
    public function info(string $service, string $identifier, string $backupId): ApiResponse
    {
        return $this->api->get($this->path('site-backup-info', $service, $identifier, $backupId));
    }

    /**
     * List a site's backups, optionally filtered to a single backup type.
     *
     * GET /site-backups-list/{service}/{identifier}[/{backup_type}]
     */
    public function list(string $service, string $identifier, ?string $backupType = null): ApiResponse
    {
        $segments = ['site-backups-list', $service, $identifier];

        if ($backupType !== null) {
            $segments[] = $backupType;
        }

        return $this->api->get($this->path(...$segments));
    }
}
