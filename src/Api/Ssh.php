<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * SSH tag — SSH/SFTP users, per-client authorized keys, and the alias public
 * key store.
 *
 * Sites are addressed by a `{service}/{identifier}` pair; key stores are
 * client-scoped.
 *
 * @see https://wp.cloud/docs/api/tags/ssh.md
 */
final class Ssh extends Resource
{
    /**
     * List the alias public-key categories for a client.
     *
     * GET /alias-pkey/categories/{client}
     */
    public function pkeyCategories(string $client): ApiResponse
    {
        return $this->api->get($this->path('alias-pkey', 'categories', $client));
    }

    /**
     * Get a single alias public key.
     *
     * GET /alias-pkey/get/{client}/{category}/{name}
     */
    public function getPkey(string $client, string $category, string $name): ApiResponse
    {
        return $this->api->get($this->path('alias-pkey', 'get', $client, $category, $name));
    }

    /**
     * List alias public keys in a category (paged via `after`).
     *
     * POST /alias-pkey/list/{client}/{category}  (body: after=…)
     */
    public function listPkeys(string $client, string $category, ?int $after = null): ApiResponse
    {
        $body = $after !== null ? ['after' => $after] : [];

        return $this->api->post($this->path('alias-pkey', 'list', $client, $category), $body);
    }

    /**
     * Remove an alias public key.
     *
     * POST /alias-pkey/remove/{client}/{category}/{name}
     */
    public function removePkey(string $client, string $category, string $name): ApiResponse
    {
        return $this->api->post($this->path('alias-pkey', 'remove', $client, $category, $name));
    }

    /**
     * Create or update an alias public key.
     *
     * POST /alias-pkey/set/{client}/{category}/{name}  (body: pkey=…)
     */
    public function setPkey(string $client, string $category, string $name, string $pkey): ApiResponse
    {
        return $this->api->post($this->path('alias-pkey', 'set', $client, $category, $name), ['pkey' => $pkey]);
    }

    /**
     * Read a client's authorized keys (action "list"), optionally one by ID.
     *
     * GET /client-authorized-keys/{client}/{action}[/{id}]
     *
     * @param string $action One of: list, add, remove.
     */
    public function authorizedKeys(string $client, string $action, ?string $id = null): ApiResponse
    {
        return $this->api->get($this->authorizedKeysPath($client, $action, $id));
    }

    /**
     * Add or remove a client's authorized keys.
     *
     * POST /client-authorized-keys/{client}/{action}[/{id}]
     *
     * @param string               $action One of: list, add, remove.
     * @param array<string, mixed> $body   authorized_keys_line, name.
     */
    public function manageAuthorizedKeys(string $client, string $action, array $body, ?string $id = null): ApiResponse
    {
        return $this->api->post($this->authorizedKeysPath($client, $action, $id), $body);
    }

    /**
     * Get details for an SSH user by username.
     *
     * GET /get-ssh-user/{username}
     */
    public function getUser(string $username): ApiResponse
    {
        return $this->api->get($this->path('get-ssh-user', $username));
    }

    /**
     * Queue a job to disconnect all SSH users for a site.
     *
     * GET /ssh-disconnect-all-users/{service}/{identifier}
     */
    public function disconnectAll(string $service, string $identifier): ApiResponse
    {
        return $this->api->get($this->path('ssh-disconnect-all-users', $service, $identifier));
    }

    /**
     * Add an SSH/SFTP user to a site.
     *
     * POST /ssh-user/{service}/{identifier}/add  (body: user, pkey, pass)
     *
     * @param array<string, mixed> $body
     */
    public function addUser(string $service, string $identifier, array $body): ApiResponse
    {
        return $this->api->post($this->path('ssh-user', $service, $identifier, 'add'), $body);
    }

    /**
     * List a site's SSH/SFTP users.
     *
     * GET /ssh-user/{service}/{identifier}/list
     */
    public function listUsers(string $service, string $identifier): ApiResponse
    {
        return $this->api->get($this->path('ssh-user', $service, $identifier, 'list'));
    }

    /**
     * Remove a site's SSH/SFTP user.
     *
     * POST /ssh-user/{service}/{identifier}/remove/{username}
     */
    public function removeUser(string $service, string $identifier, string $username): ApiResponse
    {
        return $this->api->post($this->path('ssh-user', $service, $identifier, 'remove', $username));
    }

    /**
     * Update a site's SSH/SFTP user.
     *
     * POST /ssh-user/{service}/{identifier}/update/{username}
     *
     * @param array<string, mixed> $body pkey, pass, allow-disable-password.
     */
    public function updateUser(string $service, string $identifier, string $username, array $body): ApiResponse
    {
        return $this->api->post($this->path('ssh-user', $service, $identifier, 'update', $username), $body);
    }

    private function authorizedKeysPath(string $client, string $action, ?string $id): string
    {
        $segments = ['client-authorized-keys', $client, $action];

        if ($id !== null) {
            $segments[] = $id;
        }

        return $this->path(...$segments);
    }
}
