<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Http;

use function is_array;

/**
 * Decoded WP Cloud Atomic API response envelope.
 *
 * Every endpoint returns JSON of the form `{ "message": ..., "data": ... }`.
 * `data` is usually an object (associative array), but some endpoints return a
 * scalar — e.g. /job-completion/{id} returns the status as a bare string — so it
 * is typed `mixed`.
 */
final class ApiResponse
{
    public function __construct(
        public readonly int $statusCode,
        public readonly ?string $message,
        public readonly mixed $data,
    ) {
    }

    /**
     * Read a value out of the `data` payload (when it is an object), with an
     * optional default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return is_array($this->data) ? ($this->data[$key] ?? $default) : $default;
    }
}
