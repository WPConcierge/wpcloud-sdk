<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Email tag — manage a client's blocked email domains.
 *
 * @see https://wp.cloud/docs/api/tags/email.md
 */
final class Email extends Resource
{
    /**
     * Manage blocked email domains for a client.
     *
     * GET /email-block/{client}/{action}/{type}
     *
     * @param string $action Action to perform (currently only "list").
     * @param string $type   Block type (currently only "sasl_block").
     */
    public function block(string $client, string $action = 'list', string $type = 'sasl_block'): ApiResponse
    {
        return $this->api->get($this->path('email-block', $client, $action, $type));
    }
}
