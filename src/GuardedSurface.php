<?php

/**
 * The one arm shape that stores something while refusing one request: the
 * listing beyond the end of the content graph.
 *
 * `shared()` is the whole of it. In range the arm is the Shared pair its
 * unguarded siblings declare; out of range it is the Uncacheable pair with the
 * reason the guard carried, and the same Surface is rendered either way. The
 * type has no other terminal, so a guard cannot be declared and then dropped by
 * choosing an uncacheable end — the reason is spent on the only request that
 * needs it.
 */

declare(strict_types=1);

namespace Iniznet\Mahout\Render;

final readonly class GuardedSurface
{
    public function __construct(
        private QueryContext $ctx,
        private DeclaredSurface $declared,
        private string $reason,
    ) {
    }

    /**
     * The arm's plan: Shared and wrapped in range, Uncacheable with the guard's
     * reason beyond the last page the content graph contains.
     */
    public function shared(FragmentKey $key): SurfacePlan
    {
        return $this->ctx->outOfRangePage
            ? $this->declared->uncacheable($this->reason)
            : $this->declared->shared($key);
    }
}
