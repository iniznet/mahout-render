<?php

/**
 * A guard that states no reason.
 *
 * The overflow guard is the arm's own refusal to store a page the content graph
 * does not contain, and the caching contract reads that refusal as a reason. An
 * empty one would surface only on the request that trips the guard, which is the
 * worst moment to discover the arm cannot explain itself: the declaration is
 * checked where it is written instead.
 */

declare(strict_types=1);

namespace Iniznet\Mahout\Render\Exception;

final class OverflowGuardWithoutReason extends \LogicException implements RenderException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function forGuard(): self
    {
        return new self('A guarded SurfacePlan arm must state the reason it refuses to store a page beyond the content graph.');
    }
}
