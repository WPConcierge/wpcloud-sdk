<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Base exception for non-2xx responses from the WP Cloud Atomic API.
 *
 * The API returns a `{ message, data }` envelope; the documented error codes
 * are mapped to dedicated subclasses via {@see ApiException::fromResponse()}.
 */
class ApiException extends RuntimeException
{
    /** @param array<string, mixed> $data */
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly array $data = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<string, mixed> */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Build the most specific exception for a given status code.
     *
     * @param array<string, mixed> $data
     */
    public static function fromResponse(int $statusCode, string $message, array $data = []): self
    {
        $class = match ($statusCode) {
            400     => BadRequestException::class,
            403     => ForbiddenException::class,
            404     => NotFoundException::class,
            405     => MethodNotAllowedException::class,
            409     => ConflictException::class,
            412     => PreconditionFailedException::class,
            421     => MisdirectedRequestException::class,
            423     => LockedException::class,
            default => $statusCode >= 500 ? ServerException::class : self::class,
        };

        return new $class($message, $statusCode, $data);
    }
}
