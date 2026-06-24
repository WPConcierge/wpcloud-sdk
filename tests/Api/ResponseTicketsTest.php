<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\ResponseTickets;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class ResponseTicketsTest extends ApiTestCase
{
    public function testFull(): void
    {
        (new ResponseTickets($this->client()))->full('ticket-abc');

        $this->assertPost('response-ticket/get/full');
        $this->assertBodyContains('response-ticket-id=ticket-abc');
    }

    public function testSummary(): void
    {
        (new ResponseTickets($this->client()))->summary('ticket-xyz');

        $this->assertPost('response-ticket/get/summary');
        $this->assertBodyContains('response-ticket-id=ticket-xyz');
    }

    public function testMultiStatus(): void
    {
        (new ResponseTickets($this->client()))->multiStatus(['t1', 't2']);

        $this->assertPost('response-ticket/multi-status');
        $this->assertBodyContains('response-tickets%5B0%5D=t1');
        $this->assertBodyContains('response-tickets%5B1%5D=t2');
    }
}
