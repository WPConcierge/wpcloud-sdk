<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Email;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class EmailTest extends ApiTestCase
{
    public function testBlockWithDefaultArgs(): void
    {
        (new Email($this->client()))->block('acme');

        $this->assertGet('email-block/acme/list/sasl_block');
    }

    public function testBlockWithExplicitAction(): void
    {
        (new Email($this->client()))->block('acme', 'list');

        $this->assertGet('email-block/acme/list/sasl_block');
    }

    public function testBlockWithExplicitActionAndType(): void
    {
        (new Email($this->client()))->block('acme', 'list', 'sasl_block');

        $this->assertGet('email-block/acme/list/sasl_block');
    }
}
