<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Tests\Api;

use PHPUnit\Framework\Attributes\DataProvider;
use WPConcierge\WPCloud\Api\AtomicApi;
use WPConcierge\WPCloud\Api\Backups;
use WPConcierge\WPCloud\Api\ClientMeta;
use WPConcierge\WPCloud\Api\Cron;
use WPConcierge\WPCloud\Api\CustomCertificates;
use WPConcierge\WPCloud\Api\EdgeCache;
use WPConcierge\WPCloud\Api\Email;
use WPConcierge\WPCloud\Api\Jobs;
use WPConcierge\WPCloud\Api\Logs;
use WPConcierge\WPCloud\Api\Metrics;
use WPConcierge\WPCloud\Api\ResponseTickets;
use WPConcierge\WPCloud\Api\Security;
use WPConcierge\WPCloud\Api\Servers;
use WPConcierge\WPCloud\Api\Sites;
use WPConcierge\WPCloud\Api\Ssh;
use WPConcierge\WPCloud\Api\Tasks;
use WPConcierge\WPCloud\Api\Usage;
use WPConcierge\WPCloud\Api\Webhooks;
use WPConcierge\WPCloud\Tests\ApiTestCase;

class AtomicApiTest extends ApiTestCase
{
    /**
     * @return array<string, array{string, class-string}>
     */
    public static function accessorProvider(): array
    {
        return [
            'backups'            => ['backups', Backups::class],
            'clientMeta'         => ['clientMeta', ClientMeta::class],
            'cron'               => ['cron', Cron::class],
            'customCertificates' => ['customCertificates', CustomCertificates::class],
            'edgeCache'          => ['edgeCache', EdgeCache::class],
            'email'              => ['email', Email::class],
            'jobs'               => ['jobs', Jobs::class],
            'logs'               => ['logs', Logs::class],
            'metrics'            => ['metrics', Metrics::class],
            'responseTickets'    => ['responseTickets', ResponseTickets::class],
            'security'           => ['security', Security::class],
            'servers'            => ['servers', Servers::class],
            'sites'              => ['sites', Sites::class],
            'ssh'                => ['ssh', Ssh::class],
            'tasks'              => ['tasks', Tasks::class],
            'usage'              => ['usage', Usage::class],
            'webhooks'           => ['webhooks', Webhooks::class],
        ];
    }

    /**
     * @param class-string $expected
     */
    #[DataProvider('accessorProvider')]
    public function testAccessorReturnsResourceAndMemoizes(string $method, string $expected): void
    {
        $api = new AtomicApi($this->client());

        $first = $api->{$method}();
        self::assertInstanceOf($expected, $first);
        self::assertSame($first, $api->{$method}(), 'Resource should be memoized.');
    }

    public function testCreateBuildsAFunctioningApi(): void
    {
        $api = AtomicApi::create('test-key');

        self::assertInstanceOf(Sites::class, $api->sites());
    }
}
