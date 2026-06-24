<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use WPConcierge\WPCloud\Api\Security;
use WPConcierge\WPCloud\Tests\ApiTestCase;

final class SecurityTest extends ApiTestCase
{
    public function testAddFirewallRule(): void
    {
        (new Security($this->client()))->addFirewallRule('blog.example.com', ['action' => 'allow', 'port' => '443']);

        $this->assertPost('firewall-rules/blog.example.com/add');
        $this->assertBodyContains('action=allow');
        $this->assertBodyContains('port=443');
    }

    public function testListFirewallRules(): void
    {
        (new Security($this->client()))->listFirewallRules('blog.example.com');

        $this->assertGet('firewall-rules/blog.example.com/list');
    }

    public function testRemoveFirewallRule(): void
    {
        (new Security($this->client()))->removeFirewallRule('blog.example.com', 42);

        $this->assertPost('firewall-rules/blog.example.com/remove');
        $this->assertBodyContains('rule_id=42');
    }

    public function testScanMalwareWithoutBody(): void
    {
        (new Security($this->client()))->scanMalware('blog.example.com');

        $this->assertPost('malware/scan/blog.example.com');
    }

    public function testScanMalwareWithBody(): void
    {
        (new Security($this->client()))->scanMalware('blog.example.com', ['incremental' => 'true']);

        $this->assertPost('malware/scan/blog.example.com');
        $this->assertBodyContains('incremental=true');
    }
}
