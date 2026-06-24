<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\EdgeCache;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class EdgeCacheTest extends ApiTestCase
{
    public function testGet(): void
    {
        (new EdgeCache($this->client()))->get('my-site');

        $this->assertGet('edge-cache/my-site');
    }

    public function testGetActionWithoutDomain(): void
    {
        (new EdgeCache($this->client()))->getAction('my-site', 'purge');

        $this->assertGet('edge-cache/my-site/purge');
    }

    public function testGetActionWithDomain(): void
    {
        (new EdgeCache($this->client()))->getAction('my-site', 'purge', 'example.com');

        $this->assertGet('edge-cache/my-site/purge/example.com');
    }

    public function testSetActionWithoutDomain(): void
    {
        (new EdgeCache($this->client()))->setAction('my-site', 'on');

        $this->assertPost('edge-cache/my-site/on');
    }

    public function testSetActionWithBodyAndWithoutDomain(): void
    {
        (new EdgeCache($this->client()))->setAction('my-site', 'purge', ['purge_uris' => '/path']);

        $this->assertPost('edge-cache/my-site/purge');
        $this->assertBodyContains('purge_uris=%2Fpath');
    }

    public function testSetActionWithDomain(): void
    {
        (new EdgeCache($this->client()))->setAction('my-site', 'ddos', [], 'example.com');

        $this->assertPost('edge-cache/my-site/ddos/example.com');
    }

    public function testGetDefensiveModeWithoutDomain(): void
    {
        (new EdgeCache($this->client()))->getDefensiveMode('my-site');

        $this->assertGet('edge-cache/my-site/ddos_until');
    }

    public function testGetDefensiveModeWithDomain(): void
    {
        (new EdgeCache($this->client()))->getDefensiveMode('my-site', 'example.com');

        $this->assertGet('edge-cache/my-site/ddos_until/example.com');
    }

    public function testSetDefensiveModeWithoutDomain(): void
    {
        (new EdgeCache($this->client()))->setDefensiveMode('my-site', 1700000000);

        $this->assertPost('edge-cache/my-site/ddos_until');
        $this->assertBodyContains('timestamp=1700000000');
    }

    public function testSetDefensiveModeWithDomain(): void
    {
        (new EdgeCache($this->client()))->setDefensiveMode('my-site', 1700000000, 'example.com');

        $this->assertPost('edge-cache/my-site/ddos_until/example.com');
        $this->assertBodyContains('timestamp=1700000000');
    }
}
