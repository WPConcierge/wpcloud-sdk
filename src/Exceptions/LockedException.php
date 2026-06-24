<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Exceptions;

/**
 * 423 — the resource is locked (e.g. an operation is already in progress).
 */
class LockedException extends ApiException
{
}
