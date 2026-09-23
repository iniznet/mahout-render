<?php

/**
 * The dispatch table's one pattern, written once.
 *
 * An arm of the table answers two questions and nothing else: which Surface
 * renders this request, and what may be stored. Every path out of this builder
 * names both — `shared()` is the Shared response over the Shared fragment,
 * `uncacheable()` is the pair that stores nothing and must state why — so no
 * arm can reach a plan without its `Cacheability` and its `FragmentScope`, and
 * no arm inherits a default.
 *
 * The builder is per request: the query facts it holds are the facts a guard
 * reads, and the fragment store it holds is the one its wrapped plans were
 * built with. Neither is resolved from anywhere; both arrive through the
 * constructor.
 *
 * The Surface arrives as a closure and is built by a terminal, exactly once. A
 * guarded arm needs the same Surface on both of its paths, and a table that
 * wrote `new BlogIndex(...)` twice drifts the moment one copy is edited — the
 * duplication is what this class exists to remove.
 *
 * An arm whose class or scope is not the Shared pair — a per-role or per-user
 * surface — is written with `SurfacePlan::wrapped()`, which names its class and
 * its scope in the same call. The builder states only the pairs a real caller
 * has asked for.
 */

declare(strict_types=1);

namespace Iniznet\Mahout\Render;

final readonly class SurfacePlanBuilder
{
    public function __construct(
        private QueryContext $ctx,
        private FragmentCache $cache,
    ) {
    }

    /**
     * The Surface this arm renders, named but not yet built.
     *
     * @param \Closure(): Component $surface
     */
    public function surface(\Closure $surface): DeclaredSurface
    {
        return new DeclaredSurface($this->ctx, $this->cache, $surface);
    }
}
