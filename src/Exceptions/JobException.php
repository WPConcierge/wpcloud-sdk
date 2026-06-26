<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Exceptions;

use RuntimeException;

/**
 * Base exception for job-polling failures (see Support\JobPoller).
 */
class JobException extends RuntimeException
{
}
