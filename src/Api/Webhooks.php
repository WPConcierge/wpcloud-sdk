<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Webhooks tag — inspect failed webhook delivery jobs for a client.
 *
 * @see https://wp.cloud/docs/api/tags/webhooks.md
 */
final class Webhooks extends Resource
{
    /**
     * List failed webhook jobs for a client.
     *
     * GET /webhook/failures/{client}?job_id={jobId}
     */
    public function failures(string $client, string $jobId): ApiResponse
    {
        return $this->api->get($this->path('webhook', 'failures', $client), ['job_id' => $jobId]);
    }
}
