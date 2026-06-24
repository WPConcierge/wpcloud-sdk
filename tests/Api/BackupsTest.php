<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Backups;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class BackupsTest extends ApiTestCase
{
    public function testCreate(): void
    {
        (new Backups($this->client()))->create('myblog.example.com', 'db');

        $this->assertPost('on-demand-backup/create/myblog.example.com/db');
    }

    public function testDelete(): void
    {
        (new Backups($this->client()))->delete('myblog.example.com', 'bkp-42');

        $this->assertPost('on-demand-backup/delete/myblog.example.com/bkp-42');
    }

    public function testGet(): void
    {
        (new Backups($this->client()))->get('atomic', '123', 'bkp-99');

        $this->assertGet('site-backup-get/atomic/123/bkp-99');
    }

    public function testInfo(): void
    {
        (new Backups($this->client()))->info('atomic', '123', 'bkp-99');

        $this->assertGet('site-backup-info/atomic/123/bkp-99');
    }

    public function testListWithoutBackupType(): void
    {
        (new Backups($this->client()))->list('atomic', '123');

        $this->assertGet('site-backups-list/atomic/123');
    }

    public function testListWithBackupType(): void
    {
        (new Backups($this->client()))->list('atomic', '123', 'db');

        $this->assertGet('site-backups-list/atomic/123/db');
    }
}
