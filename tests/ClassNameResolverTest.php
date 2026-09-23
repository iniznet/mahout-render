<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Render\Tests;

use Iniznet\Mahout\Render\ClassNameResolver;
use PHPUnit\Framework\TestCase;

final class ClassNameResolverTest extends TestCase
{
    public function testMarkupMayInvokeTheResolverDirectly(): void
    {
        $resolver = new SemanticResolver();

        self::assertSame('card', $resolver('card'));
        self::assertSame('card', $resolver->resolve('card'));
    }
}

final class SemanticResolver implements ClassNameResolver
{
    public function resolve(string $semantic): string
    {
        return $semantic;
    }

    public function __invoke(string $semantic): string
    {
        return $this->resolve($semantic);
    }
}
