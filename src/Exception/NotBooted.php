<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Render\Exception;

/**
 * Thrown when render machinery is reached before the kernel booted. The
 * kernel is the only permitted composition root; earlier access is a setup
 * bug, not a runtime condition.
 */
final class NotBooted extends \LogicException implements RenderException
{
    public static function service(string $service): self
    {
        return new self("The kernel has not booted: '".$service."' was reached before boot.");
    }

    public static function beforeQuery(): self
    {
        return new self('The kernel has not booted: the query context was read before the main query existed.');
    }
}
