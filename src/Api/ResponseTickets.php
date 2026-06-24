<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Api;

use WPConcierge\WPCloud\Http\ApiResponse;

/**
 * Response Tickets tag — look up the status and details of response tickets.
 *
 * @see https://wp.cloud/docs/api/tags/response-tickets.md
 */
final class ResponseTickets extends Resource
{
    /**
     * Get the full details of a response ticket.
     *
     * POST /response-ticket/get/full  (body: response-ticket-id=…)
     */
    public function full(string $responseTicketId): ApiResponse
    {
        return $this->api->post(
            $this->path('response-ticket', 'get', 'full'),
            ['response-ticket-id' => $responseTicketId],
        );
    }

    /**
     * Get a summary of a response ticket.
     *
     * POST /response-ticket/get/summary  (body: response-ticket-id=…)
     */
    public function summary(string $responseTicketId): ApiResponse
    {
        return $this->api->post(
            $this->path('response-ticket', 'get', 'summary'),
            ['response-ticket-id' => $responseTicketId],
        );
    }

    /**
     * Get the statuses of multiple response tickets at once.
     *
     * POST /response-ticket/multi-status  (body: response-tickets[]=…)
     *
     * @param list<scalar> $responseTickets
     */
    public function multiStatus(array $responseTickets): ApiResponse
    {
        return $this->api->post(
            $this->path('response-ticket', 'multi-status'),
            ['response-tickets' => $responseTickets],
        );
    }
}
