<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Exceptions;

/**
 * 412 — a precondition for the operation was not met (e.g. on-demand backups
 * still exist when deleting a site).
 */
class PreconditionFailedException extends ApiException
{
}
