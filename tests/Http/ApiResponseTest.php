<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Http;

use PHPUnit\Framework\TestCase;
use WPConcierge\WPCloud\Http\ApiResponse;

class ApiResponseTest extends TestCase
{
    public function testGetReadsKeyFromArrayData(): void
    {
        $response = new ApiResponse(200, 'OK', ['job_id' => 99]);

        self::assertSame(99, $response->get('job_id'));
    }

    public function testGetReturnsDefaultForMissingKey(): void
    {
        $response = new ApiResponse(200, 'OK', ['a' => 1]);

        self::assertSame('fallback', $response->get('missing', 'fallback'));
        self::assertNull($response->get('missing'));
    }

    public function testGetReturnsDefaultWhenDataIsScalar(): void
    {
        $response = new ApiResponse(200, 'OK', 'queued');

        self::assertSame('queued', $response->data);
        self::assertSame('def', $response->get('anything', 'def'));
    }
}
