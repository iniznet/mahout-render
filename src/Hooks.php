<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Render;

/**
 * Every hook the render pipeline emits. Names are declared once, here.
 */
final class Hooks
{
    /**
     * The surface-failure seam. The boundary fires this action when a Surface
     * throws, carrying the throwable and the diagnostics reference.
     *
     * @since 1.0
     *
     * @action
     */
    public const string SURFACE_FAILED = 'mahout/render/surface_failed';
}
