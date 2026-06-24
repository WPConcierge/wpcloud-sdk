<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Custom SSL Certificates tag — stage, validate, activate, and manage
 * customer-supplied TLS certificates for a site.
 *
 * @see https://wp.cloud/docs/api/tags/custom-ssl-certificates.md
 */
final class CustomCertificates extends Resource
{
    /**
     * Get the active custom certificate for a site, or a specific one by ID.
     *
     * GET /custom-certificates/{site}[/{id}]
     */
    public function get(string $site, ?string $id = null): ApiResponse
    {
        $segments = ['custom-certificates', $site];

        if ($id !== null) {
            $segments[] = $id;
        }

        return $this->api->get($this->path(...$segments));
    }

    /**
     * List custom certificates for a site.
     *
     * GET /custom-certificates/{site}/list
     *
     * @param array<string, scalar> $query status, limit, offset,
     *                                      expiring_within_days, order_by.
     */
    public function list(string $site, array $query = []): ApiResponse
    {
        return $this->api->get($this->path('custom-certificates', $site, 'list'), $query);
    }

    /**
     * Stage a custom certificate for later activation.
     *
     * POST /custom-certificates/{site}/stage
     *
     * @param array<string, mixed> $body certificate, private_key, domains,
     *                                    csr, trusted_certificates,
     *                                    client_certificate.
     */
    public function stage(string $site, array $body): ApiResponse
    {
        return $this->api->post($this->path('custom-certificates', $site, 'stage'), $body);
    }

    /**
     * Validate a custom certificate without staging it.
     *
     * POST /custom-certificates/{site}/validate
     *
     * @param array<string, mixed> $body
     */
    public function validate(string $site, array $body): ApiResponse
    {
        return $this->api->post($this->path('custom-certificates', $site, 'validate'), $body);
    }

    /**
     * Activate a previously staged certificate.
     *
     * POST /custom-certificates/{site}/{custom_certificate_id}/activate
     *
     * @param array<string, mixed> $body Optionally `domains`.
     */
    public function activate(string $site, string $customCertificateId, array $body = []): ApiResponse
    {
        return $this->api->post(
            $this->path('custom-certificates', $site, $customCertificateId, 'activate'),
            $body,
        );
    }

    /**
     * Deactivate an active custom certificate.
     *
     * POST /custom-certificates/{site}/{id}/deactivate
     */
    public function deactivate(string $site, string $id): ApiResponse
    {
        return $this->api->post($this->path('custom-certificates', $site, $id, 'deactivate'));
    }

    /**
     * Delete a custom certificate.
     *
     * POST /custom-certificates/{site}/{id}/delete
     */
    public function delete(string $site, string $id): ApiResponse
    {
        return $this->api->post($this->path('custom-certificates', $site, $id, 'delete'));
    }

    /**
     * Update a custom certificate.
     *
     * POST /custom-certificates/{site}/{id}/update
     *
     * @param array<string, mixed> $body
     */
    public function update(string $site, string $id, array $body): ApiResponse
    {
        return $this->api->post($this->path('custom-certificates', $site, $id, 'update'), $body);
    }
}
