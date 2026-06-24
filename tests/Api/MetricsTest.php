<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Metrics;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class MetricsTest extends ApiTestCase
{
    public function testGetWithoutSummarize(): void
    {
        (new Metrics($this->client()))->get('client', 'acme', ['start' => '2024-01-01']);

        $this->assertPost('metrics/client/acme');
        $this->assertBodyContains('start=2024-01-01');
    }

    public function testGetWithSummarize(): void
    {
        (new Metrics($this->client()))->get('site', '123', ['start' => '2024-01-01'], true);

        $this->assertPost('metrics/site/123/summarize');
        $this->assertBodyContains('start=2024-01-01');
    }

    public function testSiteWithoutSummarize(): void
    {
        (new Metrics($this->client()))->site('blog.example.com', ['start' => '2024-06-01']);

        $this->assertPost('site-metrics/blog.example.com');
        $this->assertBodyContains('start=2024-06-01');
    }

    public function testSiteWithSummarize(): void
    {
        (new Metrics($this->client()))->site('blog.example.com', ['start' => '2024-06-01'], true);

        $this->assertPost('site-metrics/blog.example.com/summarize');
        $this->assertBodyContains('start=2024-06-01');
    }
}
