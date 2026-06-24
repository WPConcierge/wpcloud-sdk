<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Servers;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class ServersTest extends ApiTestCase
{
    public function testGetAvailableDatacenters(): void
    {
        (new Servers($this->client()))->getAvailableDatacenters('acme');

        $this->assertGet('get-available-datacenters/acme');
    }

    public function testGetPhpVersionsWithoutVerbose(): void
    {
        (new Servers($this->client()))->getPhpVersions('acme');

        $this->assertGet('get-php-versions/acme');
    }

    public function testGetPhpVersionsWithVerbose(): void
    {
        (new Servers($this->client()))->getPhpVersions('acme', true);

        $this->assertGet('get-php-versions/acme/verbose');
    }
}
