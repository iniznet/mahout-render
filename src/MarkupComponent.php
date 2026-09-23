<?php

/**
 * The markup base: one markup file, one ob_start, one bound variable, no
 * extract(). A component renders typed props to HTML through the class
 * resolver's names and never fetches data, touches a global or fires a hook.
 *
 * @internal
 */

declare(strict_types=1);

namespace Iniznet\Mahout\Render;

abstract class MarkupComponent implements Component
{
    public function __construct(private readonly ClassNameResolver $classes)
    {
    }

    /** The absolute markup path this component renders. */
    abstract protected function markupPath(): string;

    public function render(): string
    {
        \ob_start();
        $c = $this->classes;
        require $this->markupPath();

        return (string) \ob_get_clean();
    }

    /** The class-name resolver markup references, as $c(). */
    protected function classes(): ClassNameResolver
    {
        return $this->classes;
    }
}
