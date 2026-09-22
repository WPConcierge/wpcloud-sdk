<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Domains tag — domain preflight and hosting eligibility checks.
 *
 * Wraps the check-can-host-domain endpoint as a dedicated resource accessor
 * so callers can reach it via `$api->domains()->checkCanHost(...)`.
 *
 * Why both this and {@see Sites::checkCanHostDomain()} exist: the endpoint
 * belongs to the Sites tag, and this SDK organises resources per WP Cloud API
 * tag, so `Sites::checkCanHostDomain()` is the canonical home for it. This
 * class is a convenience accessor kept for the `$api->domains()` reading path,
 * and it delegates to that method rather than repeating the call, so the two
 * cannot drift apart. Prefer the Sites method in new code.
 *
 * @see https://wp.cloud/docs/api/tags/sites.md
 */
final class Domains extends Resource
{
    /**
     * Check whether a client can host a domain on WP Cloud. Intended as a
     * preflight before site:create. Delegates to the canonical
     * {@see Sites::checkCanHostDomain()}, which owns the path; this method adds
     * nothing but the `$api->domains()` reading path.
     *
     * The verdict is NOT a boolean field. Read it as:
     *
     *  - Allowed  ⇔ `data.allowed === true` — `{"message":"OK","data":{"allowed":true}}`.
     *  - Refusal  ⇔ anything else, INCLUDING an empty `data`, with the reason
     *    in `message` — e.g. `{"message":"Restricted domain [wordpress.com]","data":[]}`
     *    or `{"message":"Domain name already used [x.net]. TXT record
     *    verification is required to bypass this check.","data":[]}`.
     *
     * There is no `allowed: false`. The platform never sends one — not even for
     * a domain already hosted on WordPress.com / WP Cloud, which is refused
     * with an empty `data` like any other. On a refusal the `allowed` key is
     * absent entirely. An implementer who tests for a false verdict therefore
     * writes a condition that can never match, and the preflight silently
     * passes every domain it exists to refuse.
     *
     * Concretely, {@see ApiResponse::get()} is `$this->data[$key] ?? $default`,
     * so `$response->get('allowed')` returns `null` on a refusal — not `false`
     * — and it cannot distinguish a missing key from one holding `null`. Treat
     * only a strict `true` as permission; a caller that needs to tell absence
     * from a stored null must read `->data` directly, which on a refusal is `[]`.
     *
     * GET /check-can-host-domain/{client}/{domain}
     */
    public function checkCanHost(string $client, string $domain): ApiResponse
    {
        return (new Sites($this->api))->checkCanHostDomain($client, $domain);
    }
}
