<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Webhooks;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class WebhooksTest extends ApiTestCase
{
    public function testFailures(): void
    {
        (new Webhooks($this->client()))->failures('acme', '42');

        self::assertNotNull($this->captured);
        self::assertSame('GET', $this->captured['method']);
        self::assertStringContainsString('webhook/failures/acme', $this->captured['url']);
        self::assertStringContainsString('job_id=42', $this->captured['url']);
    }
}
