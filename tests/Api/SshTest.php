<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Ssh;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class SshTest extends ApiTestCase
{
    public function testPkeyCategories(): void
    {
        (new Ssh($this->client()))->pkeyCategories('acme');

        $this->assertGet('alias-pkey/categories/acme');
    }

    public function testGetPkey(): void
    {
        (new Ssh($this->client()))->getPkey('acme', 'deploy', 'my-key');

        $this->assertGet('alias-pkey/get/acme/deploy/my-key');
    }

    public function testListPkeysWithoutAfter(): void
    {
        (new Ssh($this->client()))->listPkeys('acme', 'deploy');

        $this->assertPost('alias-pkey/list/acme/deploy');
    }

    public function testListPkeysWithAfter(): void
    {
        (new Ssh($this->client()))->listPkeys('acme', 'deploy', 99);

        $this->assertPost('alias-pkey/list/acme/deploy');
        $this->assertBodyContains('after=99');
    }

    public function testRemovePkey(): void
    {
        (new Ssh($this->client()))->removePkey('acme', 'deploy', 'my-key');

        $this->assertPost('alias-pkey/remove/acme/deploy/my-key');
    }

    public function testSetPkey(): void
    {
        (new Ssh($this->client()))->setPkey('acme', 'deploy', 'my-key', 'ssh-rsa AAAA');

        $this->assertPost('alias-pkey/set/acme/deploy/my-key');
        $this->assertBodyContains('pkey=');
    }

    public function testAuthorizedKeysWithoutId(): void
    {
        (new Ssh($this->client()))->authorizedKeys('acme', 'list');

        $this->assertGet('client-authorized-keys/acme/list');
    }

    public function testAuthorizedKeysWithId(): void
    {
        (new Ssh($this->client()))->authorizedKeys('acme', 'remove', '42');

        $this->assertGet('client-authorized-keys/acme/remove/42');
    }

    public function testManageAuthorizedKeysWithoutId(): void
    {
        (new Ssh($this->client()))->manageAuthorizedKeys('acme', 'add', ['name' => 'deploy']);

        $this->assertPost('client-authorized-keys/acme/add');
        $this->assertBodyContains('name=deploy');
    }

    public function testManageAuthorizedKeysWithId(): void
    {
        (new Ssh($this->client()))->manageAuthorizedKeys('acme', 'remove', ['name' => 'deploy'], '7');

        $this->assertPost('client-authorized-keys/acme/remove/7');
    }

    public function testGetUser(): void
    {
        (new Ssh($this->client()))->getUser('jdoe');

        $this->assertGet('get-ssh-user/jdoe');
    }

    public function testDisconnectAll(): void
    {
        (new Ssh($this->client()))->disconnectAll('atomic', '123');

        $this->assertGet('ssh-disconnect-all-users/atomic/123');
    }

    public function testAddUser(): void
    {
        (new Ssh($this->client()))->addUser('atomic', '123', ['user' => 'jdoe']);

        $this->assertPost('ssh-user/atomic/123/add');
        $this->assertBodyContains('user=jdoe');
    }

    public function testListUsers(): void
    {
        (new Ssh($this->client()))->listUsers('atomic', '123');

        $this->assertGet('ssh-user/atomic/123/list');
    }

    public function testRemoveUser(): void
    {
        (new Ssh($this->client()))->removeUser('atomic', '123', 'jdoe');

        $this->assertPost('ssh-user/atomic/123/remove/jdoe');
    }

    public function testUpdateUser(): void
    {
        (new Ssh($this->client()))->updateUser('atomic', '123', 'jdoe', ['pass' => 'secret']);

        $this->assertPost('ssh-user/atomic/123/update/jdoe');
        $this->assertBodyContains('pass=secret');
    }
}
