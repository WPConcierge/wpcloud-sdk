<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Sites;
use WPConcierge\WPCloud\Tests\ApiTestCase;

class SitesTest extends ApiTestCase
{
    public function testCreate(): void
    {
        $sites    = new Sites($this->client('{"message":"OK","data":{"job_id":123}}'));
        $response = $sites->create('acme', ['admin_user' => 'root', 'domain_name' => 'x.example']);

        $this->assertPost('create-site/acme');
        $this->assertBodyContains('admin_user=root');
        self::assertSame(123, $response->get('job_id'));
    }

    public function testListWithoutMeta(): void
    {
        (new Sites($this->client()))->list('acme');

        $this->assertGet('get-sites/acme');
    }

    public function testListWithMultipleMeta(): void
    {
        (new Sites($this->client()))->list('acme', 'php_version', '_data');

        $this->assertGet('get-sites/acme/php_version/_data');
    }

    public function testGet(): void
    {
        (new Sites($this->client()))->get('blog.example.com');

        $this->assertGet('get-site/blog.example.com');
    }

    public function testGetWithExtra(): void
    {
        (new Sites($this->client()))->get('123', true);

        $this->assertGet('get-site/123/extra');
    }

    public function testDelete(): void
    {
        (new Sites($this->client()))->delete('site', '12345');

        $this->assertPost('delete-site/site/12345');
    }

    public function testRestore(): void
    {
        (new Sites($this->client()))->restore('123', ['abc']);

        $this->assertPost('restore-site/123');
        $this->assertBodyContains('restore_from%5B0%5D=abc');
    }

    public function testCheckCanHostDomain(): void
    {
        (new Sites($this->client()))->checkCanHostDomain('acme', 'x.example');

        $this->assertGet('check-can-host-domain/acme/x.example');
    }

    public function testGetIpsWithoutDomain(): void
    {
        (new Sites($this->client()))->getIps('acme');

        $this->assertGet('get-ips/acme');
    }

    public function testGetIpsWithDomain(): void
    {
        (new Sites($this->client()))->getIps('acme', 'x.example');

        $this->assertGet('get-ips/acme/x.example');
    }

    public function testUpdateDomainWithoutKeep(): void
    {
        (new Sites($this->client()))->updateDomain('site', '123', 'new.example');

        $this->assertPost('update-site-domain/site/123/new.example');
    }

    public function testUpdateDomainWithKeep(): void
    {
        (new Sites($this->client()))->updateDomain('site', '123', 'new.example', 'keep');

        $this->assertPost('update-site-domain/site/123/new.example/keep');
    }

    public function testResetDbPasswordWithoutNew(): void
    {
        (new Sites($this->client()))->resetDbPassword('atomic', '123');

        $this->assertPost('reset-db-password/atomic/123');
    }

    public function testResetDbPasswordWithNew(): void
    {
        (new Sites($this->client()))->resetDbPassword('atomic', '123', 'secret');

        $this->assertPost('reset-db-password/atomic/123/secret');
    }

    public function testManageSoftware(): void
    {
        (new Sites($this->client()))->manageSoftware('atomic', '123', [
            'plugins/woocommerce/latest' => 'activate',
            'themes/storefront/latest'   => 'install',
        ]);

        $this->assertPost('site-manage-software/atomic/123');
        // Each slug is a top-level form key (slug=action), not wrapped under software_slug[…].
        $this->assertBodyContains('plugins%2Fwoocommerce%2Flatest=activate');
        $this->assertBodyContains('themes%2Fstorefront%2Flatest=install');
    }

    public function testSetWordPressVersion(): void
    {
        (new Sites($this->client()))->setWordPressVersion('123', 'latest');

        $this->assertPost('site-wordpress-version/123/latest');
    }

    public function testUpdateOptions(): void
    {
        (new Sites($this->client()))->updateOptions('atomic', '123', ['blogname' => 'Hi']);

        $this->assertPost('update-site-options/atomic/123');
        $this->assertBodyContains('options%5Bblogname%5D=Hi');
    }

    public function testGetMeta(): void
    {
        (new Sites($this->client()))->getMeta('123', 'php_memory_limit');

        $this->assertGet('site-meta/123/php_memory_limit/get');
    }

    public function testSetMeta(): void
    {
        (new Sites($this->client()))->setMeta('123', 'php_memory_limit', '512');

        $this->assertPost('site-meta/123/php_memory_limit/update');
        $this->assertBodyContains('value=512');
    }

    public function testRemoveMeta(): void
    {
        (new Sites($this->client()))->removeMeta('123', 'php_memory_limit');

        $this->assertGet('site-meta/123/php_memory_limit/remove');
    }

    public function testSslAndroidCompatEnable(): void
    {
        (new Sites($this->client()))->sslAndroidCompat('x.example', true);

        $this->assertPost('ssl-android-compat/x.example/true');
    }

    public function testSslHstsSubdomainDisable(): void
    {
        (new Sites($this->client()))->sslHstsSubdomain('x.example', false);

        $this->assertPost('ssl-hsts-subdomain/x.example/false');
    }

    public function testSslInfo(): void
    {
        (new Sites($this->client()))->sslInfo('x.example');

        $this->assertPost('ssl-info/x.example');
    }

    public function testAliasWithDomain(): void
    {
        (new Sites($this->client()))->alias('site', '123', 'add', 'alias.example');

        $this->assertGet('site-alias/site/123/add/alias.example');
    }

    public function testConvertDbCharset(): void
    {
        (new Sites($this->client()))->convertDbCharset('site', '123', 'latin1', 'utf8mb4');

        $this->assertGet('site-convert-db-charset/site/123/latin1/utf8mb4');
    }

    public function testSetChroot(): void
    {
        (new Sites($this->client()))->setChroot('123', 'generic');

        $this->assertPost('site-set-chroot/123/generic');
    }

    public function testSetAccessType(): void
    {
        (new Sites($this->client()))->setAccessType('atomic', '123', 'ssh');

        $this->assertPost('site-set-access-type/atomic/123/ssh');
    }
}
