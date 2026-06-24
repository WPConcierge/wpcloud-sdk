<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Logs;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class LogsTest extends ApiTestCase
{
    public function testSitePostsToCorrectPath(): void
    {
        (new Logs($this->client()))->site('my-site', 'acme', ['start' => '2024-01-01']);

        self::assertNotNull($this->captured);
        self::assertSame('POST', $this->captured['method']);
        self::assertStringContainsString('logs/site/my-site', $this->captured['url']);
    }

    public function testSiteIncludesClientQueryParam(): void
    {
        (new Logs($this->client()))->site('my-site', 'acme', []);

        self::assertNotNull($this->captured);
        self::assertStringContainsString('client=acme', $this->captured['url']);
    }

    public function testSiteIncludesBodyFields(): void
    {
        (new Logs($this->client()))->site('my-site', 'acme', ['start' => '2024-01-01', 'page_size' => '50']);

        $this->assertBodyContains('start=2024-01-01');
        $this->assertBodyContains('page_size=50');
    }

    public function testErrors(): void
    {
        (new Logs($this->client()))->errors('my-site', ['start' => '2024-01-01']);

        $this->assertPost('site-error-logs/my-site');
        $this->assertBodyContains('start=2024-01-01');
    }

    public function testWebServer(): void
    {
        (new Logs($this->client()))->webServer('my-site', ['start' => '2024-01-01']);

        $this->assertPost('site-logs/my-site');
        $this->assertBodyContains('start=2024-01-01');
    }
}
