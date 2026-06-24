<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Tasks;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class TasksTest extends ApiTestCase
{
    public function testCreateWithoutBody(): void
    {
        (new Tasks($this->client()))->create('acme', 'software');

        $this->assertPost('task-create/acme/software');
    }

    public function testCreateWithBody(): void
    {
        (new Tasks($this->client()))->create('acme', 'run-wp-cli-command', ['args' => 'plugin list']);

        $this->assertPost('task-create/acme/run-wp-cli-command');
        $this->assertBodyContains('args=');
    }

    public function testGetWithoutBody(): void
    {
        (new Tasks($this->client()))->get('task-abc-123');

        $this->assertPost('task-get/task-abc-123');
    }

    public function testGetWithBody(): void
    {
        (new Tasks($this->client()))->get('task-abc-123', ['site_count_limit' => '5']);

        $this->assertPost('task-get/task-abc-123');
        $this->assertBodyContains('site_count_limit=5');
    }

    public function testInterruptWithoutBody(): void
    {
        (new Tasks($this->client()))->interrupt('task-xyz-789');

        $this->assertPost('task-interrupt/task-xyz-789');
    }

    public function testInterruptWithBody(): void
    {
        (new Tasks($this->client()))->interrupt('task-xyz-789', ['reason' => 'manual']);

        $this->assertPost('task-interrupt/task-xyz-789');
        $this->assertBodyContains('reason=manual');
    }
}
