<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Render\Tests;

use Iniznet\Mahout\Render\Component;
use Iniznet\Mahout\Render\Stack;
use PHPUnit\Framework\TestCase;

final class StackTest extends TestCase
{
    public function testChildrenRenderInOrderAndTheStackAddsNothing(): void
    {
        $stack = new Stack([
            new FixedComponent('<p>one</p>'),
            new FixedComponent('<p>two</p>'),
        ]);

        self::assertSame('<p>one</p><p>two</p>', $stack->render());
    }

    public function testAnEmptyStackRendersNothing(): void
    {
        self::assertSame('', (new Stack())->render());
    }
}

final class FixedComponent implements Component
{
    public function __construct(private readonly string $html)
    {
    }

    public function render(): string
    {
        return $this->html;
    }
}
