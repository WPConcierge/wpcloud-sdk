# wpcloud-sdk

A complete, **1:1 PHP SDK** for the [WP Cloud](https://wp.cloud) (Automattic Atomic) API. Every endpoint in the [OpenAPI spec](https://wp.cloud/docs/api/openapi.json) — all 17 tags — is exposed as a typed method on a tag resource, reached through a single facade.

This is the reusable client extracted from [`wpc-cloud`](https://github.com/WPConcierge/wpc-cloud) (the WPConcierge CLI), packaged for Composer.

## Installation

```bash
composer require wpconcierge/wpcloud-sdk
```

Requires PHP 8.2+. Uses [`symfony/http-client`](https://symfony.com/doc/current/http_client.html) for transport.

## Usage

```php
use WPConcierge\WPCloud\Api\AtomicApi;

$api = AtomicApi::create('your-api-key');

// Create a site (async — returns a job_id)
$response = $api->sites()->create('my-client', [
    'admin_user'  => 'admin',
    'admin_email' => 'admin@example.com',
    'domain_name' => 'example.com',
    'php_version' => '8.3',
]);

$jobId = $response->get('job_id');

// Poll the job to completion
do {
    $status = $api->jobs()->completion((string) $jobId)->data;
} while (! in_array($status, ['success', 'failure'], true));

// Read a site
$site = $api->sites()->get('example.com');
echo $site->get('php_version');
```

### The response envelope

Every call returns an `ApiResponse` wrapping the API's `{ message, data }` envelope:

```php
$response->statusCode;        // int
$response->message;           // ?string
$response->data;              // mixed (array, or a scalar for some endpoints)
$response->get('key', $def);  // read a key out of an array `data`
```

### Errors

Non-2xx responses throw a typed `ApiException` subclass so you can catch what you care about:

```php
use WPConcierge\WPCloud\Exceptions\NotFoundException;
use WPConcierge\WPCloud\Exceptions\ApiException;

try {
    $api->sites()->get('does-not-exist');
} catch (NotFoundException $e) {
    // 404
} catch (ApiException $e) {
    $e->getStatusCode(); // int
    $e->getData();       // array<string, mixed> — the error `data` payload
}
```

| Status | Exception |
|--------|-----------|
| 400 | `BadRequestException` |
| 403 | `ForbiddenException` |
| 404 | `NotFoundException` |
| 405 | `MethodNotAllowedException` |
| 409 | `ConflictException` |
| 412 | `PreconditionFailedException` |
| 421 | `MisdirectedRequestException` |
| 423 | `LockedException` |
| 5xx | `ServerException` |

### Custom HTTP client / testing

Inject your own `ApiClient` (e.g. with a mock transport) instead of using `create()`:

```php
use Symfony\Component\HttpClient\MockHttpClient;
use WPConcierge\WPCloud\Api\AtomicApi;
use WPConcierge\WPCloud\Http\ApiClient;

$api = new AtomicApi(new ApiClient(new MockHttpClient(/* ... */), 'test-key'));
```

## Resource map

Reach each [API tag](https://wp.cloud/docs/api/) through its accessor on `AtomicApi`:

| Accessor | Tag | Examples |
|----------|-----|----------|
| `sites()` | Sites | `create`, `get`, `list`, `delete`, `restore`, `updateDomain`, `getMeta`, `setMeta`, `sslInfo`, `setWordPressVersion`, … |
| `servers()` | Servers | `getPhpVersions`, `getAvailableDatacenters` |
| `jobs()` | Jobs | `completion`, `status`, `statuses`, `statusesBatch` |
| `backups()` | Backups | `create`, `delete`, `get`, `info`, `list` |
| `clientMeta()` | Client Meta | `add`, `get`, `update`, `remove` |
| `cron()` | Cron | `add`, `list`, `update`, `remove` |
| `customCertificates()` | Custom SSL Certificates | `stage`, `validate`, `activate`, `deactivate`, `update`, `delete`, `get`, `list` |
| `edgeCache()` | Edge Cache | `get`, `getAction`, `setAction`, `getDefensiveMode`, `setDefensiveMode` |
| `email()` | Email | `block` |
| `logs()` | Logs | `site`, `errors`, `webServer` |
| `metrics()` | Metrics | `get`, `site` |
| `responseTickets()` | Response Tickets | `full`, `summary`, `multiStatus` |
| `ssh()` | SSH | `addUser`, `listUsers`, `updateUser`, `removeUser`, `getPkey`, `setPkey`, `authorizedKeys`, … |
| `security()` | Security | `addFirewallRule`, `listFirewallRules`, `removeFirewallRule`, `scanMalware` |
| `tasks()` | Tasks | `create`, `get`, `interrupt` |
| `usage()` | Usage | `get` |
| `webhooks()` | Webhooks | `failures` |

## Conventions

- **Site addressing.** Three conventions appear across endpoints, mirrored in the method signatures: `{client}` (client-scoped), `{site}` (site ID *or* domain), and `{service}/{identifier}` (service + ID/domain).
- **Async.** Mutating endpoints return a `job_id`; poll `jobs()->completion()` / `jobs()->status()`.
- **Optional path segments.** Where the API has an optional trailing segment (`/verbose`, `/extra`, `/summarize`, an optional `{domain}`, etc.), it's a parameter with a sensible default rather than a separate method.
- **Form encoding.** Request bodies are sent as `application/x-www-form-urlencoded` with PHP array notation for nested values — exactly what the API expects.

## Development

```bash
composer install
make check   # cs-fix + cs + quality (phpstan)
make test    # phpunit
```

## License

MIT — see [LICENSE](LICENSE).
