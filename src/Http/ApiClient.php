<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Http;

use Generator;
use JsonException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use WPConcierge\WPCloud\Exceptions\ApiException;

use function array_key_exists;
use function is_array;
use function json_decode;
use function ltrim;
use function rtrim;

/**
 * Thin client for the WP Cloud Atomic API.
 *
 * - Authenticates with an API key sent in the `auth` header (OpenAPI ApiKeyAuth).
 * - Sends request bodies as application/x-www-form-urlencoded; nested arrays are
 *   encoded in PHP form-array notation (e.g. software[key]=, data[key][value]=),
 *   which is exactly what the API expects.
 * - Unwraps the `{ message, data }` envelope into an {@see ApiResponse}.
 * - Maps documented non-2xx status codes to typed {@see ApiException} subclasses.
 *
 * @see https://wp.cloud/docs/api/agent-guide.md
 */
class ApiClient
{
    public const DEFAULT_BASE_URL = 'https://atomic-api.wordpress.com/api/v1.0/';

    private string $baseUrl;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        string $baseUrl = self::DEFAULT_BASE_URL,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    /**
     * Convenience constructor that wires up a default Symfony HTTP client.
     *
     * Use the primary constructor instead when you need to inject a custom or
     * mock {@see HttpClientInterface} (e.g. in tests).
     */
    public static function create(string $apiKey, string $baseUrl = self::DEFAULT_BASE_URL): self
    {
        return new self(HttpClient::create(), $apiKey, $baseUrl);
    }

    /**
     * Issue a GET request.
     *
     * @param array<string, scalar> $query
     */
    public function get(string $path, array $query = []): ApiResponse
    {
        return $this->request('GET', $path, ['query' => $query]);
    }

    /**
     * Issue a POST request with a form-encoded body and optional query string.
     *
     * @param array<string, mixed>  $body
     * @param array<string, scalar> $query
     */
    public function post(string $path, array $body = [], array $query = []): ApiResponse
    {
        $options = ['body' => $body];

        if ($query !== []) {
            $options['query'] = $query;
        }

        return $this->request('POST', $path, $options);
    }

    /**
     * Stream a raw binary response body (site-backup-get).
     *
     * The ordinary {@see request} path is unusable for these endpoints on two
     * counts: it buffers the whole body via getContent(), and it json_decode()s
     * it. A backup body is raw bytes (gzipped SQL / bzip2 tar) running to
     * hundreds of MB, so decoding throws and buffering would exhaust memory.
     *
     * The status line and headers are read eagerly here — so an error surfaces
     * as the usual typed {@see ApiException} before the caller commits to a
     * stream — while the body stays lazy in {@see BinaryStream::$chunks}.
     *
     * @param array<string, scalar> $query
     */
    public function download(string $path, array $query = []): BinaryStream
    {
        try {
            $response   = $this->httpClient->request('GET', $this->url($path), [
                'query'   => $query,
                'headers' => [
                    'auth' => $this->apiKey,
                    // Naming Accept-Encoding ourselves is load-bearing, not
                    // cosmetic. A db backup IS a gzip file, and WP Cloud serves
                    // it as `Content-Encoding: gzip` with a Content-Length of
                    // the *compressed* size. Left to manage the header itself,
                    // the transport turns on curl's transparent decoding and
                    // silently inflates the body — yielding 66MB of plain SQL
                    // for a backup whose Content-Length (and backup:list `bytes`)
                    // both say 9.7MB. Setting the header explicitly suppresses
                    // that decoding, so the bytes we hand back are the bytes on
                    // the wire and Content-Length is telling the truth.
                    // (fs backups are bzip2 with no Content-Encoding, so this is
                    // a no-op for them.)
                    'Accept-Encoding' => 'gzip',
                ],
            ]);
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new ApiException('Transport error contacting WP Cloud API: ' . $e->getMessage(), 0, [], $e);
        }

        if ($statusCode >= 400) {
            // Error responses are the ordinary JSON envelope, and small enough
            // to buffer — hand them to the shared decoder, which always throws.
            $this->decode($statusCode, $response->getContent(throw: false));

            throw ApiException::fromResponse($statusCode, 'WP Cloud API request failed.');
        }

        return new BinaryStream($statusCode, $response->getHeaders(throw: false), $this->chunks($response));
    }

    /**
     * Lazily yield the response body, chunk by chunk.
     *
     * @return Generator<int, string>
     */
    private function chunks(ResponseInterface $response): Generator
    {
        try {
            foreach ($this->httpClient->stream($response) as $chunk) {
                $content = $chunk->getContent();

                if ($content !== '') {
                    yield $content;
                }
            }
        } catch (TransportExceptionInterface $e) {
            throw new ApiException('Transport error streaming from WP Cloud API: ' . $e->getMessage(), 0, [], $e);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    private function request(string $method, string $path, array $options): ApiResponse
    {
        $options['headers']['auth'] = $this->apiKey;

        try {
            $response   = $this->httpClient->request($method, $this->url($path), $options);
            $statusCode = $response->getStatusCode();
            $content    = $response->getContent(throw: false);
        } catch (TransportExceptionInterface $e) {
            throw new ApiException('Transport error contacting WP Cloud API: ' . $e->getMessage(), 0, [], $e);
        }

        return $this->decode($statusCode, $content);
    }

    private function decode(int $statusCode, string $content): ApiResponse
    {
        $decoded = [];

        if ($content !== '') {
            try {
                $parsed  = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                $decoded = is_array($parsed) ? $parsed : [];
            } catch (JsonException $e) {
                if ($statusCode >= 400) {
                    throw ApiException::fromResponse($statusCode, 'Unparseable error response from WP Cloud API.');
                }

                throw new ApiException('Unparseable response from WP Cloud API: ' . $e->getMessage(), $statusCode);
            }
        }

        $message = isset($decoded['message']) ? (string) $decoded['message'] : null;
        $data    = array_key_exists('data', $decoded) ? $decoded['data'] : [];

        if ($statusCode >= 400) {
            $errorData = is_array($data) ? $data : [];

            throw ApiException::fromResponse($statusCode, $message ?? 'WP Cloud API request failed.', $errorData);
        }

        return new ApiResponse($statusCode, $message, $data);
    }

    private function url(string $path): string
    {
        return $this->baseUrl . ltrim($path, '/');
    }
}
