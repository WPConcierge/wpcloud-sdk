<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Cron;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class CronTest extends ApiTestCase
{
    public function testAdd(): void
    {
        (new Cron($this->client()))->add('myblog.example.com', '0 * * * *', 'wp cron event run --due-now');

        $this->assertPost('crontab/myblog.example.com/add');
        $this->assertBodyContains('schedule=0+%2A+%2A+%2A+%2A');
        $this->assertBodyContains('command=wp+cron+event+run+--due-now');
    }

    public function testList(): void
    {
        (new Cron($this->client()))->list('myblog.example.com');

        $this->assertGet('crontab/myblog.example.com/list');
    }

    public function testRemove(): void
    {
        (new Cron($this->client()))->remove('myblog.example.com', 7);

        $this->assertPost('crontab/myblog.example.com/remove');
        $this->assertBodyContains('cron_id=7');
    }

    public function testUpdate(): void
    {
        (new Cron($this->client()))->update('myblog.example.com', 7, 'wp cron event run --due-now');

        $this->assertPost('crontab/myblog.example.com/update/7');
        $this->assertBodyContains('command=wp+cron+event+run+--due-now');
    }
}
