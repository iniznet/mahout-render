<?php

/**
 * The header map a response carries: core's payload narrowed to its documented
 * shape, and the declared policy merged over it last.
 *
 * The two facts under test are the two that make one response have one answer:
 * nothing survives the narrowing that cannot reach the wire, and the policy is
 * the last write, so no subscriber can widen a response the cacheability class
 * bounded.
 */

declare(strict_types=1);

namespace Iniznet\Mahout\Render\Tests;

use Iniznet\Mahout\Render\Cache\HeaderPolicy;
use Iniznet\Mahout\Render\Cache\ResponseHeaders;
use Iniznet\Mahout\Render\Cacheability;
use Iniznet\Mahout\Render\Exception\MalformedHeaderMap;
use PHPUnit\Framework\TestCase;

final class ResponseHeadersTest extends TestCase
{
    public function testTheDocumentedShapeSurvivesTheNarrowingUnchanged(): void
    {
        $narrowed = ResponseHeaders::narrow([
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Robots-Tag' => 'noindex',
            'Last-Modified' => false,
        ]);

        self::assertSame([
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Robots-Tag' => 'noindex',
            'Last-Modified' => false,
        ], $narrowed);
    }

    public function testAnEmptyMapNarrowsToAnEmptyMap(): void
    {
        self::assertSame([], ResponseHeaders::narrow([]));
    }

    public function testAnEntryKeyedByAnOffsetIsRefused(): void
    {
        try {
            ResponseHeaders::narrow(['Vary' => 'Cookie', 3 => 'a value with no name']);
            self::fail('an entry keyed by an integer offset must be refused, never named from the offset.');
        } catch (MalformedHeaderMap $e) {
            self::assertSame(3, $e->offset());
            self::assertStringContainsString('offset 3', $e->getMessage());
        }
    }

    public function testAnEntryCarryingAnUnrepresentableValueIsRefused(): void
    {
        try {
            ResponseHeaders::narrow(['Cache-Control' => ['public']]);
            self::fail('a structured value must be refused, never coerced to a string.');
        } catch (MalformedHeaderMap $e) {
            self::assertSame('Cache-Control', $e->offset());
            self::assertStringContainsString('Cache-Control', $e->getMessage());
        }
    }

    public function testThePolicyIsMergedLastSoExactlyOneStatementSurvives(): void
    {
        $merged = ResponseHeaders::merge(
            ResponseHeaders::narrow([
                'Content-Type' => 'text/html; charset=UTF-8',
                'Cache-Control' => 'no-cache',
                'Vary' => 'Accept-Encoding',
            ]),
            HeaderPolicy::derive(Cacheability::Private, 'GET', false),
        );

        self::assertSame('private, max-age=60, must-revalidate', $merged['Cache-Control'], 'a subscriber cannot widen the declared class.');
        self::assertSame('Cookie', $merged['Vary']);
        self::assertSame('text/html; charset=UTF-8', $merged['Content-Type'], 'core owns the headers this policy does not state.');
    }

    public function testAnUncacheablePolicyUnsetsLastModifiedOverCoreValue(): void
    {
        $merged = ResponseHeaders::merge(
            ResponseHeaders::narrow(['Last-Modified' => 'Wed, 11 Jan 1995 10:00:00 GMT']),
            HeaderPolicy::derive(Cacheability::Uncacheable, 'GET', false),
        );

        self::assertFalse($merged['Last-Modified'], 'false reaches core as the instruction to remove the header.');
        self::assertSame('private, no-store, max-age=0', $merged['Cache-Control']);
    }

    public function testASharedPolicyAddsNoVaryToCoreMap(): void
    {
        $merged = ResponseHeaders::merge(
            ResponseHeaders::narrow(['Content-Type' => 'text/html; charset=UTF-8']),
            HeaderPolicy::derive(Cacheability::Shared, 'GET', false),
        );

        self::assertArrayNotHasKey('Vary', $merged);
        self::assertArrayNotHasKey('Expires', $merged, 'core owns Expires and Pragma; the policy never states them.');
        self::assertArrayNotHasKey('Pragma', $merged);
    }
}
