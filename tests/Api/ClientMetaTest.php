<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\ClientMeta;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class ClientMetaTest extends ApiTestCase
{
    public function testAdd(): void
    {
        (new ClientMeta($this->client()))->add('acme', 'php_memory_limit', '512');

        $this->assertPost('client-meta/acme/php_memory_limit/add');
        $this->assertBodyContains('value=512');
    }

    public function testGet(): void
    {
        (new ClientMeta($this->client()))->get('acme', 'php_memory_limit');

        $this->assertGet('client-meta/acme/php_memory_limit/get');
    }

    public function testRemove(): void
    {
        (new ClientMeta($this->client()))->remove('acme', 'php_memory_limit');

        $this->assertGet('client-meta/acme/php_memory_limit/remove');
    }

    public function testUpdate(): void
    {
        (new ClientMeta($this->client()))->update('acme', 'php_memory_limit', '256');

        $this->assertPost('client-meta/acme/php_memory_limit/update');
        $this->assertBodyContains('value=256');
    }
}
