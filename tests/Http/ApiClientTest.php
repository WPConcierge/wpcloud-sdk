<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Http;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use WPConcierge\WPCloud\Exceptions\ApiException;
use WPConcierge\WPCloud\Exceptions\BadRequestException;
use WPConcierge\WPCloud\Exceptions\LockedException;
use WPConcierge\WPCloud\Exceptions\NotFoundException;
use WPConcierge\WPCloud\Exceptions\ServerException;
use WPConcierge\WPCloud\Http\ApiClient;
use WPConcierge\WPCloud\Tests\ApiTestCase;

class ApiClientTest extends ApiTestCase
{
    public function testGetUnwrapsEnvelope(): void
    {
        $client   = $this->client('{"message":"Hello","data":{"foo":"bar"}}');
        $response = $client->get('some-path');

        self::assertSame(200, $response->statusCode);
        self::assertSame('Hello', $response->message);
        self::assertSame(['foo' => 'bar'], $response->data);
        self::assertGet('some-path');
    }

    public function testGetAppendsQueryStringToUrl(): void
    {
        $client = $this->client();
        $client->get('webhook/failures/acme', ['job_id' => '42']);

        self::assertNotNull($this->captured);
        self::assertStringContainsString('job_id=42', $this->captured['url']);
    }

    public function testPostSendsFormEncodedBody(): void
    {
        $client = $this->client();
        $client->post('create-site/acme', ['admin_user' => 'root', 'admin_email' => 'a@b.c']);

        $this->assertPost('create-site/acme');
        $this->assertBodyContains('admin_user=root');
        $this->assertBodyContains('admin_email=');
    }

    public function testPostEncodesNestedArraysInPhpFormNotation(): void
    {
        $client = $this->client();
        $client->post('update-site-options/atomic/123', ['options' => ['blogname' => 'Hi']]);

        $this->assertBodyContains('options%5Bblogname%5D=Hi');
    }

    public function testPostMergesQueryString(): void
    {
        $client = $this->client();
        $client->post('logs/site/123', ['start' => 1], ['client' => 'acme']);

        self::assertNotNull($this->captured);
        self::assertStringContainsString('client=acme', $this->captured['url']);
    }

    public function testAuthHeaderIsSent(): void
    {
        $captured = null;
        $http     = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = $options['headers'] ?? [];

            return new MockResponse('{"message":"OK","data":[]}');
        });

        (new ApiClient($http, 'secret-key'))->get('ping');

        self::assertContains('auth: secret-key', $captured);
    }

    public function testTrailingSlashOnBaseUrlIsNormalised(): void
    {
        (new ApiClient(
            new MockHttpClient(function (string $method, string $url) {
                $this->captured = ['method' => $method, 'url' => $url, 'body' => ''];

                return new MockResponse('{"message":"OK","data":[]}');
            }),
            'k',
            'https://example.test/api/v1.0',
        ))->get('thing');

        self::assertNotNull($this->captured);
        self::assertSame('https://example.test/api/v1.0/thing', $this->captured['url']);
    }

    /**
     * @return list<array{int, class-string<ApiException>}>
     */
    public static function errorStatusProvider(): array
    {
        return [
            [400, BadRequestException::class],
            [404, NotFoundException::class],
            [423, LockedException::class],
            [500, ServerException::class],
            [503, ServerException::class],
        ];
    }

    /**
     * @param class-string<ApiException> $expected
     */
    #[DataProvider('errorStatusProvider')]
    public function testMapsErrorStatusToTypedException(int $status, string $expected): void
    {
        $client = $this->client('{"message":"Nope","data":{"reason":"x"}}', $status);

        try {
            $client->get('boom');
            self::fail('Expected an ApiException.');
        } catch (ApiException $e) {
            self::assertInstanceOf($expected, $e);
            self::assertSame($status, $e->getStatusCode());
            self::assertSame(['reason' => 'x'], $e->getData());
            self::assertSame('Nope', $e->getMessage());
        }
    }

    public function testUnparseableErrorBodyStillThrows(): void
    {
        $this->expectException(BadRequestException::class);

        $this->client('<html>not json</html>', 400)->get('x');
    }
}
