<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Jobs tag — status of asynchronous operations.
 *
 * Mutating endpoints return a `job_id`; these endpoints report its progress.
 * Single-job and batch (multiple `jobs`) variants are both supported.
 *
 * @see https://wp.cloud/docs/api/tags/jobs.md
 */
final class Jobs extends Resource
{
    /**
     * Lightweight completion check — `data` is the bare status string
     * (e.g. "queued", "running", "success", "failure").
     *
     * GET /job-completion/{id}
     */
    public function completion(string $id): ApiResponse
    {
        return $this->api->get($this->path('job-completion', $id));
    }

    /**
     * Batch completion check for multiple jobs.
     *
     * POST /job-completion/{id}  (body: jobs[]=…)
     *
     * @param list<scalar> $jobs
     */
    public function completionBatch(string $id, array $jobs): ApiResponse
    {
        return $this->api->post($this->path('job-completion', $id), ['jobs' => $jobs]);
    }

    /**
     * Full job object including `_status` plus data and metadata.
     *
     * GET /job-status/{id}
     */
    public function status(string $id): ApiResponse
    {
        return $this->api->get($this->path('job-status', $id));
    }

    /**
     * Full job objects for all of the caller's recent jobs.
     *
     * GET /job-status
     */
    public function statuses(): ApiResponse
    {
        return $this->api->get('job-status');
    }

    /**
     * Full job objects for a specific batch of job IDs.
     *
     * POST /job-status  (body: jobs[]=…)
     *
     * @param list<scalar> $jobs
     */
    public function statusesBatch(array $jobs): ApiResponse
    {
        return $this->api->post('job-status', ['jobs' => $jobs]);
    }

    /**
     * Full job objects for a batch, scoped by a leading job ID.
     *
     * POST /job-status/{id}  (body: jobs[]=…)
     *
     * @param list<scalar> $jobs
     */
    public function statusBatch(string $id, array $jobs): ApiResponse
    {
        return $this->api->post($this->path('job-status', $id), ['jobs' => $jobs]);
    }
}
