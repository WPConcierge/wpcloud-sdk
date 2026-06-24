<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Edge Cache tag — read and update a site's edge cache and defensive (DDoS)
 * mode settings, optionally scoped to a single domain.
 *
 * @see https://wp.cloud/docs/api/tags/edge-cache.md
 */
final class EdgeCache extends Resource
{
    /**
     * Get a site's edge cache settings.
     *
     * GET /edge-cache/{site}
     */
    public function get(string $site): ApiResponse
    {
        return $this->api->get($this->path('edge-cache', $site));
    }

    /**
     * Get edge cache settings for a specific action, optionally per-domain.
     *
     * GET /edge-cache/{site}/{action}[/{domain}]
     *
     * @param string $action One of: get, purge, ddos_until.
     */
    public function getAction(string $site, string $action, ?string $domain = null): ApiResponse
    {
        return $this->api->get($this->actionPath($site, $action, $domain));
    }

    /**
     * Update edge cache settings for an action, optionally per-domain.
     *
     * POST /edge-cache/{site}/{action}[/{domain}]
     *
     * @param string               $action One of: on, off, ddos, purge, ddos_until.
     * @param array<string, mixed> $body   e.g. `purge_uris`, `timestamp`.
     */
    public function setAction(string $site, string $action, array $body = [], ?string $domain = null): ApiResponse
    {
        return $this->api->post($this->actionPath($site, $action, $domain), $body);
    }

    /**
     * Get the current Defensive (DDoS) Mode status, optionally per-domain.
     *
     * GET /edge-cache/{site}/ddos_until[/{domain}]
     */
    public function getDefensiveMode(string $site, ?string $domain = null): ApiResponse
    {
        return $this->api->get($this->actionPath($site, 'ddos_until', $domain));
    }

    /**
     * Configure Defensive (DDoS) Mode until a given UNIX timestamp, optionally
     * per-domain.
     *
     * POST /edge-cache/{site}/ddos_until[/{domain}]  (body: timestamp=…)
     */
    public function setDefensiveMode(string $site, int $timestamp, ?string $domain = null): ApiResponse
    {
        return $this->api->post($this->actionPath($site, 'ddos_until', $domain), ['timestamp' => $timestamp]);
    }

    private function actionPath(string $site, string $action, ?string $domain): string
    {
        $segments = ['edge-cache', $site, $action];

        if ($domain !== null) {
            $segments[] = $domain;
        }

        return $this->path(...$segments);
    }
}
