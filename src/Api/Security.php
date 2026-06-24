<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Security tag — site firewall rules and malware scanning.
 *
 * @see https://wp.cloud/docs/api/tags/security.md
 */
final class Security extends Resource
{
    /**
     * Add a firewall rule to a site.
     *
     * POST /firewall-rules/{site}/add
     *
     * @param array<string, mixed> $body direction, action, protocol, port,
     *                                    destination.
     */
    public function addFirewallRule(string $site, array $body): ApiResponse
    {
        return $this->api->post($this->path('firewall-rules', $site, 'add'), $body);
    }

    /**
     * List a site's firewall rules.
     *
     * GET /firewall-rules/{site}/list
     */
    public function listFirewallRules(string $site): ApiResponse
    {
        return $this->api->get($this->path('firewall-rules', $site, 'list'));
    }

    /**
     * Remove a firewall rule by ID.
     *
     * POST /firewall-rules/{site}/remove  (body: rule_id=…)
     */
    public function removeFirewallRule(string $site, int $ruleId): ApiResponse
    {
        return $this->api->post($this->path('firewall-rules', $site, 'remove'), ['rule_id' => $ruleId]);
    }

    /**
     * Scan a site for malware (async → returns job_id).
     *
     * POST /malware/scan/{site}
     *
     * @param array<string, mixed> $body log, patterns, virus, fuzzy-patterns,
     *                                    experimental, incremental, detailed.
     */
    public function scanMalware(string $site, array $body = []): ApiResponse
    {
        return $this->api->post($this->path('malware', 'scan', $site), $body);
    }
}
