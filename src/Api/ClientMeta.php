<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Client Meta tag — arbitrary key/value metadata stored against a client.
 *
 * @see https://wp.cloud/docs/api/tags/client-meta.md
 */
final class ClientMeta extends Resource
{
    /**
     * Add a client meta value.
     *
     * POST /client-meta/{client}/{key}/add  (body: value=…)
     */
    public function add(string $client, string $key, string $value): ApiResponse
    {
        return $this->api->post($this->path('client-meta', $client, $key, 'add'), ['value' => $value]);
    }

    /**
     * Read a client meta value.
     *
     * GET /client-meta/{client}/{key}/get
     */
    public function get(string $client, string $key): ApiResponse
    {
        return $this->api->get($this->path('client-meta', $client, $key, 'get'));
    }

    /**
     * Remove a client meta value.
     *
     * GET /client-meta/{client}/{key}/remove
     */
    public function remove(string $client, string $key): ApiResponse
    {
        return $this->api->get($this->path('client-meta', $client, $key, 'remove'));
    }

    /**
     * Update a client meta value.
     *
     * POST /client-meta/{client}/{key}/update  (body: value=…)
     */
    public function update(string $client, string $key, string $value): ApiResponse
    {
        return $this->api->post($this->path('client-meta', $client, $key, 'update'), ['value' => $value]);
    }
}
