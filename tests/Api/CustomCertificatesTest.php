<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\CustomCertificates;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class CustomCertificatesTest extends ApiTestCase
{
    public function testGetWithoutId(): void
    {
        (new CustomCertificates($this->client()))->get('myblog.example.com');

        $this->assertGet('custom-certificates/myblog.example.com');
    }

    public function testGetWithId(): void
    {
        (new CustomCertificates($this->client()))->get('myblog.example.com', 'cert-42');

        $this->assertGet('custom-certificates/myblog.example.com/cert-42');
    }

    public function testList(): void
    {
        (new CustomCertificates($this->client()))->list('myblog.example.com');

        $this->assertGet('custom-certificates/myblog.example.com/list');
    }

    public function testStage(): void
    {
        (new CustomCertificates($this->client()))->stage(
            'myblog.example.com',
            ['certificate' => 'CERT', 'private_key' => 'KEY'],
        );

        $this->assertPost('custom-certificates/myblog.example.com/stage');
        $this->assertBodyContains('certificate=CERT');
    }

    public function testValidate(): void
    {
        (new CustomCertificates($this->client()))->validate(
            'myblog.example.com',
            ['certificate' => 'CERT', 'private_key' => 'KEY'],
        );

        $this->assertPost('custom-certificates/myblog.example.com/validate');
        $this->assertBodyContains('certificate=CERT');
    }

    public function testActivate(): void
    {
        (new CustomCertificates($this->client()))->activate('myblog.example.com', 'cert-42');

        $this->assertPost('custom-certificates/myblog.example.com/cert-42/activate');
    }

    public function testDeactivate(): void
    {
        (new CustomCertificates($this->client()))->deactivate('myblog.example.com', 'cert-42');

        $this->assertPost('custom-certificates/myblog.example.com/cert-42/deactivate');
    }

    public function testDelete(): void
    {
        (new CustomCertificates($this->client()))->delete('myblog.example.com', 'cert-42');

        $this->assertPost('custom-certificates/myblog.example.com/cert-42/delete');
    }

    public function testUpdate(): void
    {
        (new CustomCertificates($this->client()))->update(
            'myblog.example.com',
            'cert-42',
            ['certificate' => 'CERT', 'private_key' => 'KEY'],
        );

        $this->assertPost('custom-certificates/myblog.example.com/cert-42/update');
        $this->assertBodyContains('certificate=CERT');
    }
}
