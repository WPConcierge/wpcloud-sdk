<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Http;

use function is_numeric;

/**
 * A streamed binary response body.
 *
 * A few WP Cloud endpoints (site-backup-get) return raw bytes rather than the
 * `{ message, data }` JSON envelope — a gzipped SQL dump (db) or a bzip2 tar of
 * ./htdocs (fs). Those payloads run to hundreds of megabytes, so the body is
 * exposed as a lazy chunk iterator instead of a decoded {@see ApiResponse}:
 * callers pass it straight through to a socket or a file handle and never hold
 * the whole thing in memory.
 *
 * The status line and headers have already been received and validated by the
 * time this object exists; only the body is still in flight.
 */
final class BinaryStream
{
    /**
     * @param array<string, list<string>> $headers Upstream response headers (lower-cased names).
     * @param iterable<string>            $chunks  Lazy body chunks, in order.
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly array $headers,
        public readonly iterable $chunks,
    ) {
    }

    /**
     * Upstream Content-Type, when the server sent one.
     */
    public function contentType(): ?string
    {
        return $this->header('content-type');
    }

    /**
     * Upstream Content-Length in bytes, when the server sent one.
     *
     * Absent on a chunked response, so callers must treat null as "unknown
     * size" rather than "empty" (e.g. render an indeterminate progress bar).
     */
    public function contentLength(): ?int
    {
        $value = $this->header('content-length');

        return is_numeric($value) ? (int) $value : null;
    }

    private function header(string $name): ?string
    {
        return $this->headers[$name][0] ?? null;
    }
}
