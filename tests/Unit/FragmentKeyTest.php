<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Render\Tests;

use Iniznet\Mahout\Render\Exception\EmptyKeyPart;
use Iniznet\Mahout\Render\FragmentKey;
use PHPUnit\Framework\TestCase;

final class FragmentKeyTest extends TestCase
{
    public function testTheKeyIsDeterministicAndSalted(): void
    {
        $a = FragmentKey::fromParts('single', 42, 1);
        $b = FragmentKey::fromParts('single', 42, 1);

        self::assertSame($a->toString(), $b->toString());
        self::assertStringStartsWith('v1|', $a->toString());
    }

    public function testDifferentPartsProduceDifferentKeys(): void
    {
        self::assertNotSame(
            FragmentKey::fromParts('single', 1)->toString(),
            FragmentKey::fromParts('single', 2)->toString(),
        );
    }

    public function testAnEmptyStringPartIsRefused(): void
    {
        $this->expectException(EmptyKeyPart::class);
        FragmentKey::fromParts('single', '');
    }
}
