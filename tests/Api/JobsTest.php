<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Jobs;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class JobsTest extends ApiTestCase
{
    public function testCompletion(): void
    {
        (new Jobs($this->client()))->completion('42');

        $this->assertGet('job-completion/42');
    }

    public function testCompletionBatch(): void
    {
        (new Jobs($this->client()))->completionBatch('42', ['abc', 'def']);

        $this->assertPost('job-completion/42');
        $this->assertBodyContains('jobs%5B0%5D=abc');
        $this->assertBodyContains('jobs%5B1%5D=def');
    }

    public function testStatus(): void
    {
        (new Jobs($this->client()))->status('42');

        $this->assertGet('job-status/42');
    }

    public function testStatuses(): void
    {
        (new Jobs($this->client()))->statuses();

        $this->assertGet('job-status');
    }

    public function testStatusesBatch(): void
    {
        (new Jobs($this->client()))->statusesBatch(['abc', 'def']);

        $this->assertPost('job-status');
        $this->assertBodyContains('jobs%5B0%5D=abc');
        $this->assertBodyContains('jobs%5B1%5D=def');
    }

    public function testStatusBatch(): void
    {
        (new Jobs($this->client()))->statusBatch('42', ['abc', 'def']);

        $this->assertPost('job-status/42');
        $this->assertBodyContains('jobs%5B0%5D=abc');
        $this->assertBodyContains('jobs%5B1%5D=def');
    }
}
