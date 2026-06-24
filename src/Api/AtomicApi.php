<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiClient;

/**
 * Entry point and facade for the WP Cloud Atomic API.
 *
 * Reach each API tag through a typed resource accessor, e.g.
 * `$api->sites()->create(...)`. Resources are lazily created and memoized.
 *
 * Construct with {@see AtomicApi::create()} for the common case, or inject a
 * pre-built {@see ApiClient} (e.g. with a mock HTTP client) for testing.
 *
 * @see https://wp.cloud/docs/api/
 */
final class AtomicApi
{
    private ?Backups $backups = null;

    private ?ClientMeta $clientMeta = null;

    private ?Cron $cron = null;

    private ?CustomCertificates $customCertificates = null;

    private ?EdgeCache $edgeCache = null;

    private ?Email $email = null;

    private ?Jobs $jobs = null;

    private ?Logs $logs = null;

    private ?Metrics $metrics = null;

    private ?ResponseTickets $responseTickets = null;

    private ?Security $security = null;

    private ?Servers $servers = null;

    private ?Sites $sites = null;

    private ?Ssh $ssh = null;

    private ?Tasks $tasks = null;

    private ?Usage $usage = null;

    private ?Webhooks $webhooks = null;

    public function __construct(private readonly ApiClient $client)
    {
    }

    /**
     * Build an API client from an API key (and optional base URL), wiring up a
     * default Symfony HTTP client.
     */
    public static function create(string $apiKey, string $baseUrl = ApiClient::DEFAULT_BASE_URL): self
    {
        return new self(ApiClient::create($apiKey, $baseUrl));
    }

    public function backups(): Backups
    {
        return $this->backups ??= new Backups($this->client);
    }

    public function clientMeta(): ClientMeta
    {
        return $this->clientMeta ??= new ClientMeta($this->client);
    }

    public function cron(): Cron
    {
        return $this->cron ??= new Cron($this->client);
    }

    public function customCertificates(): CustomCertificates
    {
        return $this->customCertificates ??= new CustomCertificates($this->client);
    }

    public function edgeCache(): EdgeCache
    {
        return $this->edgeCache ??= new EdgeCache($this->client);
    }

    public function email(): Email
    {
        return $this->email ??= new Email($this->client);
    }

    public function jobs(): Jobs
    {
        return $this->jobs ??= new Jobs($this->client);
    }

    public function logs(): Logs
    {
        return $this->logs ??= new Logs($this->client);
    }

    public function metrics(): Metrics
    {
        return $this->metrics ??= new Metrics($this->client);
    }

    public function responseTickets(): ResponseTickets
    {
        return $this->responseTickets ??= new ResponseTickets($this->client);
    }

    public function security(): Security
    {
        return $this->security ??= new Security($this->client);
    }

    public function servers(): Servers
    {
        return $this->servers ??= new Servers($this->client);
    }

    public function sites(): Sites
    {
        return $this->sites ??= new Sites($this->client);
    }

    public function ssh(): Ssh
    {
        return $this->ssh ??= new Ssh($this->client);
    }

    public function tasks(): Tasks
    {
        return $this->tasks ??= new Tasks($this->client);
    }

    public function usage(): Usage
    {
        return $this->usage ??= new Usage($this->client);
    }

    public function webhooks(): Webhooks
    {
        return $this->webhooks ??= new Webhooks($this->client);
    }
}
