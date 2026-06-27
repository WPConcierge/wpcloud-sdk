<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Sites tag — the core of the API: site lifecycle, domains, IPs, database,
 * software, meta, SSL, and aliases.
 *
 * Sites are addressed three ways depending on the endpoint: `{client}`
 * (client-scoped), `{site}` (site ID or domain), and `{service}/{identifier}`
 * (service + ID/domain). Mutating endpoints are usually asynchronous and return
 * a `job_id` in their `data` payload.
 *
 * @see https://wp.cloud/docs/api/tags/sites.md
 */
final class Sites extends Resource
{
    // ---------------------------------------------------------------------
    // Lifecycle
    // ---------------------------------------------------------------------

    /**
     * Create a site for a client (async → returns job_id, atomic_site_id,
     * domain_name).
     *
     * POST /create-site/{client}
     *
     * @param array<string, mixed> $body admin_user, admin_email, domain_name,
     *                                    php_version, clone_from, software,
     *                                    meta, restore_from, …
     */
    public function create(string $client, array $body): ApiResponse
    {
        return $this->api->post($this->path('create-site', $client), $body);
    }

    /**
     * List a client's sites, optionally including one or more meta keys. Each
     * requested meta key is appended as its own path segment and comes back as
     * a flat field on every site object.
     *
     * GET /get-sites/{client}[/{meta}/{meta}/…]
     */
    public function list(string $client, string ...$meta): ApiResponse
    {
        return $this->api->get($this->path('get-sites', $client, ...$meta));
    }

    /**
     * Get a single site's details by site ID or domain name. Pass
     * `$extra = true` for the expanded payload.
     *
     * GET /get-site/{site}[/extra]
     */
    public function get(string $site, bool $extra = false): ApiResponse
    {
        $segments = ['get-site', $site];

        if ($extra) {
            $segments[] = 'extra';
        }

        return $this->api->get($this->path(...$segments));
    }

    /**
     * Mark a site for deletion (async → returns job_id).
     *
     * Guard responses: 400 (do_not_delete / duplicate), 412 (on-demand backups
     * exist), 423 (locked) — surfaced as typed {@see \WPConcierge\WPCloud\Exceptions\ApiException}.
     *
     * POST /delete-site/{service}/{identifier}
     *
     * @param array<string, mixed> $body
     */
    public function delete(string $service, string $identifier, array $body = []): ApiResponse
    {
        return $this->api->post($this->path('delete-site', $service, $identifier), $body);
    }

    /**
     * Restore an existing site from its own backups (async → returns job_id).
     *
     * POST /restore-site/{site}  (body: restore_from[]=…)
     *
     * @param list<scalar> $restoreFrom
     */
    public function restore(string $site, array $restoreFrom): ApiResponse
    {
        return $this->api->post($this->path('restore-site', $site), ['restore_from' => $restoreFrom]);
    }

    // ---------------------------------------------------------------------
    // Domains, IPs & eligibility
    // ---------------------------------------------------------------------

    /**
     * Check whether a client can host a domain on WP Cloud. Intended as a
     * preflight before {@see create()}. `data.allowed` is the boolean verdict.
     *
     * GET /check-can-host-domain/{client}/{domain}
     */
    public function checkCanHostDomain(string $client, string $domain): ApiResponse
    {
        return $this->api->get($this->path('check-can-host-domain', $client, $domain));
    }

    /**
     * Get a domain verification code for a client/domain.
     *
     * GET /get-domain-verification-code/{client}/{domain}
     */
    public function getDomainVerificationCode(string $client, string $domain): ApiResponse
    {
        return $this->api->get($this->path('get-domain-verification-code', $client, $domain));
    }

    /**
     * Get a client's IP addresses, optionally scoped to a single domain.
     *
     * GET /get-ips/{client}[/{domain}]
     */
    public function getIps(string $client, ?string $domain = null): ApiResponse
    {
        $segments = ['get-ips', $client];

        if ($domain !== null) {
            $segments[] = $domain;
        }

        return $this->api->get($this->path(...$segments));
    }

    /**
     * Change a site's primary domain (async → returns job_id). Pass `$keep` to
     * retain the previous domain as an alias.
     *
     * POST /update-site-domain/{service}/{identifier}/{domain}[/{keep}]
     */
    public function updateDomain(
        string $service,
        string $identifier,
        string $domain,
        ?string $keep = null,
    ): ApiResponse {
        $segments = ['update-site-domain', $service, $identifier, $domain];

        if ($keep !== null) {
            $segments[] = $keep;
        }

        return $this->api->post($this->path(...$segments));
    }

    /**
     * Manage a site's domain aliases (read).
     *
     * GET /site-alias/{service}/{identifier}/{action}[/{domain}]
     *
     * @param string $action One of: list, add, remove.
     */
    public function alias(
        string $service,
        string $identifier,
        string $action,
        ?string $domain = null,
    ): ApiResponse {
        $segments = ['site-alias', $service, $identifier, $action];

        if ($domain !== null) {
            $segments[] = $domain;
        }

        return $this->api->get($this->path(...$segments));
    }

    // ---------------------------------------------------------------------
    // Database
    // ---------------------------------------------------------------------

    /**
     * Reset a site's database password (async → returns job_id). Pass `$new` to
     * set a specific password instead of generating one.
     *
     * POST /reset-db-password/{type}/{site}[/{new}]
     *
     * @param string $type "atomic" (external clients) or "wpcom".
     */
    public function resetDbPassword(string $type, string $site, ?string $new = null): ApiResponse
    {
        $segments = ['reset-db-password', $type, $site];

        if ($new !== null) {
            $segments[] = $new;
        }

        return $this->api->post($this->path(...$segments));
    }

    /**
     * Convert a site's database character set (async → returns job_id).
     *
     * GET /site-convert-db-charset/{service}/{identifier}/{from}/{to}
     */
    public function convertDbCharset(string $service, string $identifier, string $from, string $to): ApiResponse
    {
        return $this->api->get($this->path('site-convert-db-charset', $service, $identifier, $from, $to));
    }

    /**
     * Get a one-time phpMyAdmin URL for a site.
     *
     * POST /site-phpmyadmin/{site}
     */
    public function phpMyAdmin(string $site): ApiResponse
    {
        return $this->api->post($this->path('site-phpmyadmin', $site));
    }

    // ---------------------------------------------------------------------
    // Software, meta & options
    // ---------------------------------------------------------------------

    /**
     * Install, activate, lock, or remove site software (async → returns job_id).
     *
     * POST /site-manage-software/{type}/{site}
     * Body: a `software_slug[<slug>]=<action>` map, e.g.
     *   software_slug[plugins/woocommerce/latest]=activate
     *
     * @param  string  $type  "atomic" (external clients) or "wpcom".
     * @param  array<string, string>  $software  Slug => action map. Slugs are
     *   `plugins/<slug>/<version>`, `themes/<slug>`, `mu-plugins/<slug>/<version>`,
     *   or a `plugins://url` / `theme://url` form. Actions: install, activate,
     *   activate-locked, install-locked, deactivate, remove, lock, unlock
     *   (mu-plugins support install, install-locked, remove, lock, unlock).
     */
    public function manageSoftware(string $type, string $site, array $software): ApiResponse
    {
        return $this->api->post(
            $this->path('site-manage-software', $type, $site),
            ['software_slug' => $software],
        );
    }

    /**
     * Set a site's WordPress version (async → returns job_id).
     *
     * POST /site-wordpress-version/{site}/{version}
     */
    public function setWordPressVersion(string $site, string $version): ApiResponse
    {
        return $this->api->post($this->path('site-wordpress-version', $site, $version));
    }

    /**
     * Update site options (async → returns job_id).
     *
     * POST /update-site-options/{type}/{site}  (body: options[…]=…)
     *
     * @param string              $type    "atomic" (external clients) or "wpcom".
     * @param array<string, mixed> $options
     */
    public function updateOptions(string $type, string $site, array $options): ApiResponse
    {
        return $this->api->post($this->path('update-site-options', $type, $site), ['options' => $options]);
    }

    /**
     * Add persistent site data — values that survive site rebuilds.
     *
     * POST /site-persist-data/{site}  (body: data[…]=…)
     *
     * @param array<string, mixed> $data
     */
    public function persistData(string $site, array $data): ApiResponse
    {
        return $this->api->post($this->path('site-persist-data', $site), ['data' => $data]);
    }

    /**
     * Read a single meta value for a site (synchronous). `data` is the bare
     * value (e.g. "2", or the `_data` JSON string).
     *
     * GET /site-meta/{site}/{key}/get
     */
    public function getMeta(string $site, string $key): ApiResponse
    {
        return $this->api->get($this->path('site-meta', $site, $key, 'get'));
    }

    /**
     * Set a single meta value for a site (synchronous).
     *
     * POST /site-meta/{site}/{key}/update  (body: value=…)
     */
    public function setMeta(string $site, string $key, string $value): ApiResponse
    {
        return $this->api->post($this->path('site-meta', $site, $key, 'update'), ['value' => $value]);
    }

    /**
     * Remove a single meta value for a site.
     *
     * GET /site-meta/{site}/{key}/remove
     */
    public function removeMeta(string $site, string $key): ApiResponse
    {
        return $this->api->get($this->path('site-meta', $site, $key, 'remove'));
    }

    // ---------------------------------------------------------------------
    // SSL
    // ---------------------------------------------------------------------

    /**
     * Toggle Android compatibility for a domain's certificate.
     *
     * POST /ssl-android-compat/{domain}/{enable}
     */
    public function sslAndroidCompat(string $domain, bool $enable): ApiResponse
    {
        return $this->api->post($this->path('ssl-android-compat', $domain, $this->flag($enable)));
    }

    /**
     * Disable the HSTS preload directive for a domain.
     *
     * POST /ssl-hsts-preload/{domain}/false
     */
    public function sslHstsPreloadDisable(string $domain): ApiResponse
    {
        return $this->api->post($this->path('ssl-hsts-preload', $domain, 'false'));
    }

    /**
     * Toggle the HSTS includeSubDomains directive for a domain.
     *
     * POST /ssl-hsts-subdomain/{domain}/{enable}
     */
    public function sslHstsSubdomain(string $domain, bool $enable): ApiResponse
    {
        return $this->api->post($this->path('ssl-hsts-subdomain', $domain, $this->flag($enable)));
    }

    /**
     * Fetch SSL certificate information for a domain.
     *
     * POST /ssl-info/{domain}
     */
    public function sslInfo(string $domain): ApiResponse
    {
        return $this->api->post($this->path('ssl-info', $domain));
    }

    /**
     * Retry SSL provisioning for a domain.
     *
     * POST /ssl-retry/{domain}
     */
    public function sslRetry(string $domain): ApiResponse
    {
        return $this->api->post($this->path('ssl-retry', $domain));
    }

    /**
     * Toggle the social-crawler redirect for a domain.
     *
     * POST /ssl-social-crawler-redirect/{domain}/{enable}
     */
    public function sslSocialCrawlerRedirect(string $domain, bool $enable): ApiResponse
    {
        return $this->api->post($this->path('ssl-social-crawler-redirect', $domain, $this->flag($enable)));
    }

    // ---------------------------------------------------------------------
    // Operations
    // ---------------------------------------------------------------------

    /**
     * Configure a site to accept an incoming SSH migration.
     *
     * POST /site-allow-ssh-migration/{site}
     *
     * @param array<string, mixed> $body
     */
    public function allowSshMigration(string $site, array $body = []): ApiResponse
    {
        return $this->api->post($this->path('site-allow-ssh-migration', $site), $body);
    }

    /**
     * Update a site's chroot.
     *
     * POST /site-set-chroot/{site}/{desired}
     *
     * @param string $desired Chroot slug to set (e.g. "generic").
     */
    public function setChroot(string $site, string $desired): ApiResponse
    {
        return $this->api->post($this->path('site-set-chroot', $site, $desired));
    }

    /**
     * Set a site's SSH/SFTP access type. End-user sites default to SFTP-only; a
     * shell (full SSH) must be enabled per site.
     *
     * POST /site-set-access-type/{service}/{identifier}/{type}
     *
     * @param string $type Access type to set (e.g. "sftp", "ssh").
     */
    public function setAccessType(string $service, string $identifier, string $type): ApiResponse
    {
        return $this->api->post($this->path('site-set-access-type', $service, $identifier, $type));
    }
}
