<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Http;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use WPConcierge\WPCloud\Exceptions\NotFoundException;
use WPConcierge\WPCloud\Http\ApiClient;

use function gzencode;
use function implode;
use function iterator_to_array;
use function str_repeat;

/**
 * Binary downloads (site-backup-get).
 *
 * These payloads are raw bytes — a gzipped SQL dump (db) or a bzip2 tar (fs) —
 * not the JSON envelope every other endpoint returns. They are also large
 * (hundreds of MB), so the body must never be buffered whole.
 */
final class ApiClientDownloadTest extends TestCase
{
    /**
     * @param array{method: string, url: string, options: array<string, mixed>}|null $captured
     */
    private function client(MockResponse $response, ?array &$captured = null): ApiClient
    {
        $http = new MockHttpClient(function (string $method, string $url, array $options) use ($response, &$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return $response;
        });

        return new ApiClient($http, 'test-key');
    }

    public function testDownloadReturnsRawBytesWithoutJsonDecoding(): void
    {
        // A real db backup: gzipped SQL. json_decode() on these bytes throws —
        // which is exactly why request()/get() cannot serve this endpoint.
        $payload = (string) gzencode('CREATE TABLE wp_posts (id INT);');

        $stream = $this->client(new MockResponse($payload, ['http_code' => 200]))
            ->download('site-backup-get/atomic/555123/bkp-99');

        self::assertSame($payload, implode('', iterator_to_array($stream->chunks, false)));
    }

    public function testDownloadSendsTheAuthHeader(): void
    {
        $captured = null;
        $stream   = $this->client(new MockResponse('bytes', ['http_code' => 200]), $captured)
            ->download('site-backup-get/atomic/555123/bkp-99');

        // Drain so the request is actually issued.
        iterator_to_array($stream->chunks, false);

        self::assertSame('GET', $captured['method']);
        self::assertSame('https://atomic-api.wordpress.com/api/v1.0/site-backup-get/atomic/555123/bkp-99', $captured['url']);
        self::assertContains('auth: test-key', $captured['options']['headers']);
    }

    public function testDownloadSuppressesTransparentDecompression(): void
    {
        $captured = null;
        $stream   = $this->client(new MockResponse('bytes', ['http_code' => 200]), $captured)
            ->download('site-backup-get/atomic/555123/bkp-99');

        iterator_to_array($stream->chunks, false);

        // Verified against live WP Cloud 2026-07-12: a db backup comes back as
        // `Content-Encoding: gzip` with a Content-Length of the compressed size.
        // Without an explicit Accept-Encoding the transport auto-inflates it, so
        // we'd write 66MB of SQL while advertising 9.7MB. Dropping this header
        // silently corrupts every db download — hence the guard.
        self::assertContains('Accept-Encoding: gzip', $captured['options']['headers']);
    }

    public function testDownloadExposesUpstreamHeaders(): void
    {
        $stream = $this->client(new MockResponse('bytes', [
            'http_code'        => 200,
            'response_headers' => ['content-type' => 'application/gzip', 'content-length' => '5'],
        ]))->download('site-backup-get/atomic/555123/bkp-99');

        self::assertSame('application/gzip', $stream->contentType());
        self::assertSame(5, $stream->contentLength());
    }

    public function testDownloadReassemblesAChunkedBodyInOrder(): void
    {
        // A body delivered as several chunks, the way a large backup arrives off
        // the wire. Each must be yielded in order and none dropped.
        $parts = [str_repeat('A', 8192), str_repeat('B', 8192), 'tail'];

        $stream = $this->client(new MockResponse((static fn (): iterable => yield from $parts)(), ['http_code' => 200]))
            ->download('site-backup-get/atomic/555123/bkp-99');

        self::assertSame($parts, iterator_to_array($stream->chunks, false));
    }

    public function testDownloadThrowsTypedExceptionOnErrorStatusBeforeStreaming(): void
    {
        // Errors still come back as the JSON envelope, and must surface as the
        // usual typed exception — eagerly, so a caller can return a clean error
        // instead of a half-written file.
        $body = '{"message":"Backup not found.","data":[]}';

        $this->expectException(NotFoundException::class);

        $this->client(new MockResponse($body, ['http_code' => 404]))
            ->download('site-backup-get/atomic/555123/nope');
    }
}
