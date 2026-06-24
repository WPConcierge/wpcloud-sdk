<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Cron tag — manage a site's crontab entries.
 *
 * @see https://wp.cloud/docs/api/tags/cron.md
 */
final class Cron extends Resource
{
    /**
     * Add a cron entry.
     *
     * POST /crontab/{site}/add  (body: schedule=…, command=…)
     */
    public function add(string $site, string $schedule, string $command): ApiResponse
    {
        return $this->api->post(
            $this->path('crontab', $site, 'add'),
            ['schedule' => $schedule, 'command' => $command],
        );
    }

    /**
     * List a site's cron entries.
     *
     * GET /crontab/{site}/list
     */
    public function list(string $site): ApiResponse
    {
        return $this->api->get($this->path('crontab', $site, 'list'));
    }

    /**
     * Remove a cron entry by ID.
     *
     * POST /crontab/{site}/remove  (body: cron_id=…)
     */
    public function remove(string $site, int $cronId): ApiResponse
    {
        return $this->api->post($this->path('crontab', $site, 'remove'), ['cron_id' => $cronId]);
    }

    /**
     * Update a cron entry's command.
     *
     * POST /crontab/{site}/update/{cron_id}  (body: command=…)
     */
    public function update(string $site, int $cronId, string $command): ApiResponse
    {
        return $this->api->post(
            $this->path('crontab', $site, 'update', (string) $cronId),
            ['command' => $command],
        );
    }
}
