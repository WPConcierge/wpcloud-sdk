<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use WPConcierge\WPCloud\Http\ApiClient;

use function is_string;

/**
 * Base test case for resource and client tests.
 *
 * Provides a mock-backed {@see ApiClient} that records the outgoing request
 * (method, URL — query string included — and form-encoded body) so tests can
 * assert exactly what each resource method sends over the wire.
 */
abstract class ApiTestCase extends TestCase
{
    protected const BASE_URL = 'https://atomic-api.wordpress.com/api/v1.0/';

    /** @var array{method: string, url: string, body: string}|null */
    protected ?array $captured = null;

    /**
     * Build an ApiClient whose transport records the request and returns the
     * given canned JSON envelope.
     */
    protected function client(string $json = '{"message":"OK","data":[]}', int $code = 200): ApiClient
    {
        $this->captured = null;

        $http = new MockHttpClient(function (string $method, string $url, array $options) use ($json, $code): MockResponse {
            $body = $options['body'] ?? '';

            $this->captured = [
                'method' => $method,
                'url'    => $url,
                'body'   => is_string($body) ? $body : '',
            ];

            return new MockResponse($json, ['http_code' => $code]);
        });

        return new ApiClient($http, 'test-key');
    }

    /**
     * Assert the recorded request was a GET to the given path (relative to the
     * API base URL).
     */
    protected function assertGet(string $path): void
    {
        self::assertNotNull($this->captured, 'No request was captured.');
        self::assertSame('GET', $this->captured['method']);
        self::assertSame(self::BASE_URL . $path, $this->captured['url']);
    }

    /**
     * Assert the recorded request was a POST to the given path (relative to the
     * API base URL).
     */
    protected function assertPost(string $path): void
    {
        self::assertNotNull($this->captured, 'No request was captured.');
        self::assertSame('POST', $this->captured['method']);
        self::assertSame(self::BASE_URL . $path, $this->captured['url']);
    }

    /**
     * Assert the recorded request body contains the given form-encoded fragment.
     */
    protected function assertBodyContains(string $needle): void
    {
        self::assertNotNull($this->captured, 'No request was captured.');
        self::assertStringContainsString($needle, $this->captured['body']);
    }

    /**
     * Assert the recorded request sent no body.
     */
    protected function assertNoBody(): void
    {
        self::assertNotNull($this->captured, 'No request was captured.');
        self::assertSame('', $this->captured['body']);
    }
}
