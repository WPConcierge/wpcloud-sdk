<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Tasks tag — long-running, fleet-wide tasks (software runs, file finds,
 * WP-CLI commands, scans).
 *
 * @see https://wp.cloud/docs/api/tags/tasks.md
 */
final class Tasks extends Resource
{
    /**
     * Create a task for a client.
     *
     * POST /task-create/{client}/{type}
     *
     * @param string               $type "software", "site-find-files",
     *                                    "run-wp-cli-command", or "wpcloud-scan".
     * @param array<string, mixed> $body send_webhook_for, software, pattern,
     *                                    args, site_count_limit, site_run_list,
     *                                    cli_mode, scan_type.
     */
    public function create(string $client, string $type, array $body = []): ApiResponse
    {
        return $this->api->post($this->path('task-create', $client, $type), $body);
    }

    /**
     * Get a task's details.
     *
     * POST /task-get/{atomic_task_id}
     *
     * @param array<string, mixed> $body
     */
    public function get(string $atomicTaskId, array $body = []): ApiResponse
    {
        return $this->api->post($this->path('task-get', $atomicTaskId), $body);
    }

    /**
     * Interrupt a running task.
     *
     * POST /task-interrupt/{task_id}
     *
     * @param array<string, mixed> $body
     */
    public function interrupt(string $taskId, array $body = []): ApiResponse
    {
        return $this->api->post($this->path('task-interrupt', $taskId), $body);
    }
}
