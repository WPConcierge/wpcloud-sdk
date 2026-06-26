<?php

declare(strict_types=1);

namespace WPConcierge\WPCloud\Support;

use Closure;
use WPConcierge\WPCloud\Api\Jobs;
use WPConcierge\WPCloud\Exceptions\JobFailedException;
use WPConcierge\WPCloud\Exceptions\JobTimeoutException;

use function in_array;
use function sleep;
use function sprintf;
use function strtolower;

/**
 * Polls an asynchronous WP Cloud job until it reaches a terminal state.
 *
 * Reusable primitive for every caller that kicks off an async operation
 * (create-site, delete-site, …) and wants to wait for the returned job_id.
 *
 * The sleeper is injectable so tests can run without real delays.
 */
final class JobPoller
{
    private const TERMINAL_SUCCESS = ['success', 'complete', 'completed', 'done', 'ok'];

    private const TERMINAL_FAILURE = ['failure', 'failed', 'error'];

    public function __construct(
        private readonly Jobs $jobs,
        private readonly ?Closure $sleeper = null,
    ) {
    }

    /**
     * Poll until the job finishes, returning its final status string.
     *
     * @param  (callable(string, int): void)|null  $onTick  called after each poll with (status, elapsedSeconds)
     * @throws JobFailedException  When the job reports a terminal failure status.
     * @throws JobTimeoutException When the job does not finish within $timeout seconds.
     */
    public function wait(string $jobId, int $interval = 2, int $timeout = 300, ?callable $onTick = null): string
    {
        $elapsed = 0;

        while (true) {
            $status = (string) $this->jobs->completion($jobId)->data;

            if ($onTick !== null) {
                $onTick($status, $elapsed);
            }

            $normalized = strtolower($status);

            if (in_array($normalized, self::TERMINAL_SUCCESS, true)) {
                return $status;
            }

            if (in_array($normalized, self::TERMINAL_FAILURE, true)) {
                throw new JobFailedException(sprintf('Job %s failed with status "%s".', $jobId, $status));
            }

            if ($elapsed >= $timeout) {
                throw new JobTimeoutException(
                    sprintf('Job %s did not complete within %ds (last status "%s").', $jobId, $timeout, $status),
                );
            }

            $this->pause($interval);
            $elapsed += $interval;
        }
    }

    private function pause(int $seconds): void
    {
        if ($this->sleeper !== null) {
            ($this->sleeper)($seconds);

            return;
        }

        sleep($seconds);
    }
}
