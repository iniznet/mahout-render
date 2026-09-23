<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Render;

/**
 * The one class-name resolver markup references. The consumer decides what a
 * semantic name becomes: the name itself for a semantic build, a build-emitted
 * hashed name for a css-modules build.
 */
interface ClassNameResolver
{
    /** The class attribute value a semantic name resolves to. */
    public function resolve(string $semantic): string;

    /** Markup invokes the resolver directly: class="<?= $c('card') ?>". */
    public function __invoke(string $semantic): string;
}
