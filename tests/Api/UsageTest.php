<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Usage;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class UsageTest extends ApiTestCase
{
    public function testGet(): void
    {
        (new Usage($this->client()))->get('blog.example.com', ['start' => 1, 'end' => 100]);

        $this->assertPost('usage/blog.example.com');
        $this->assertBodyContains('start=1');
    }
}
