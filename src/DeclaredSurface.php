<?php

/**
 * One arm of the dispatch table, past the point where its Surface is named and
 * before the point where its cacheability is declared.
 *
 * There are two terminals and each names its class and its scope: the Shared
 * pair, and the pair that stores nothing with a stated reason. An arm that
 * refuses to store a page beyond the content graph goes through
 * `guardOverflow()` first, which returns the one type that can still end in a
 * stored plan — a guard whose reason is then dropped is a guard that was never
 * stated, so the builder gives it nowhere to be dropped.
 */

declare(strict_types=1);

namespace Iniznet\Mahout\Render;

use Iniznet\Mahout\Render\Exception\OverflowGuardWithoutReason;

final readonly class DeclaredSurface
{
    /**
     * @param \Closure(): Component $surface
     */
    public function __construct(
        private QueryContext $ctx,
        private FragmentCache $cache,
        private \Closure $surface,
    ) {
    }

    /**
     * The identical render for every anonymous visitor, stored under the Shared
     * fragment scope.
     */
    public function shared(FragmentKey $key): SurfacePlan
    {
        return SurfacePlan::wrapped(
            surface: $this->build(),
            cacheability: Cacheability::Shared,
            fragmentScope: FragmentScope::Shared,
            key: $key,
            cache: $this->cache,
        );
    }

    /**
     * The arm stores nothing. The reason is required, and it is the reason the
     * caching contract reads: an unbounded key space, a per-user statement, or
     * a statement about the present moment.
     */
    public function uncacheable(string $reason): SurfacePlan
    {
        return SurfacePlan::uncacheable(surface: $this->build(), reason: $reason);
    }

    /**
     * The listing arms' shared guard: a page beyond the content graph is a key
     * space an anonymous visitor can invent, so the arm refuses to store it and
     * renders the same Surface uncached instead.
     *
     * The reason is the one the unguarded branch would otherwise repeat, and it
     * is required for the same reason `uncacheable()` requires one.
     */
    public function guardOverflow(string $reason): GuardedSurface
    {
        if ('' === $reason) {
            throw OverflowGuardWithoutReason::forGuard();
        }

        return new GuardedSurface($this->ctx, $this, $reason);
    }

    /** The arm's Surface, built at the terminal and therefore built exactly once. */
    private function build(): Component
    {
        return ($this->surface)();
    }
}
