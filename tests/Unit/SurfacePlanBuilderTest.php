<?php

/**
 * The dispatch table's one pattern, asserted arm by arm.
 *
 * The gate this class exists for is the declared pair: every plan the builder
 * produces names its `Cacheability` and its `FragmentScope`, and every arm that
 * stores nothing names a reason. The tests below are the proof that the pattern
 * cannot be left half-written — an arm that guards without a reason, or refuses
 * without one, throws where it is declared.
 */

declare(strict_types=1);

namespace Iniznet\Mahout\Render\Tests;

use Iniznet\Mahout\Render\Cacheability;
use Iniznet\Mahout\Render\CachedFragment;
use Iniznet\Mahout\Render\Component;
use Iniznet\Mahout\Render\Exception\OverflowGuardWithoutReason;
use Iniznet\Mahout\Render\Exception\UncacheableWithoutReason;
use Iniznet\Mahout\Render\FragmentCache;
use Iniznet\Mahout\Render\FragmentKey;
use Iniznet\Mahout\Render\FragmentScope;
use Iniznet\Mahout\Render\QueryContext;
use Iniznet\Mahout\Render\QueryKind;
use Iniznet\Mahout\Render\SiteProfile;
use Iniznet\Mahout\Render\SurfacePlan;
use Iniznet\Mahout\Render\SurfacePlanBuilder;
use PHPUnit\Framework\TestCase;

final class SurfacePlanBuilderTest extends TestCase
{
    private int $builds = 0;

    public function testSharedArmEndToEnd(): void
    {
        $plan = $this->builder()->surface($this->component())->shared(self::key());

        self::assertInstanceOf(SurfacePlan::class, $plan);
        self::assertInstanceOf(CachedFragment::class, $plan->surface, 'a Shared arm is wrapped in one construction site.');
        self::assertSame(Cacheability::Shared, $plan->cacheability);
        self::assertSame(FragmentScope::Shared, $plan->fragmentScope);
        self::assertNotNull($plan->key, 'a stored arm carries the key it is stored under.');
        self::assertSame(self::key()->toString(), $plan->key?->toString());
    }

    public function testAnUncacheableArmDeclaresItsPairAndStatesItsReason(): void
    {
        $plan = $this->builder()->surface($this->component())->uncacheable('free-text term, unbounded key space');

        self::assertSame(Cacheability::Uncacheable, $plan->cacheability);
        self::assertSame(FragmentScope::Never, $plan->fragmentScope);
        self::assertSame('free-text term, unbounded key space', $plan->reason);
        self::assertNull($plan->key, 'an arm that stores nothing carries no key.');
        self::assertInstanceOf(PlanFixtureComponent::class, $plan->surface, 'the bare Surface is rendered, not wrapped.');
    }

    public function testAnUncacheableArmWithoutAReasonIsRefused(): void
    {
        $this->expectException(UncacheableWithoutReason::class);

        $this->builder()->surface($this->component())->uncacheable('');
    }

    public function testAGuardWithoutAReasonIsRefusedWhereItIsDeclared(): void
    {
        $this->expectException(OverflowGuardWithoutReason::class);

        $this->builder()->surface($this->component())->guardOverflow('');
    }

    public function testAGuardedArmInRequestTheSharedPair(): void
    {
        $plan = $this->builder(outOfRange: false)
            ->surface($this->component())
            ->guardOverflow('page beyond the content graph, out of range')
            ->shared(self::key());

        self::assertSame(Cacheability::Shared, $plan->cacheability);
        self::assertSame(FragmentScope::Shared, $plan->fragmentScope);
        self::assertInstanceOf(CachedFragment::class, $plan->surface);
        self::assertSame('', $plan->reason, 'the guard spends its reason only on the request that trips it.');
    }

    public function testAGuardedArmOutOfRangeIsTheUncacheablePairWithTheGuardsReason(): void
    {
        $plan = $this->builder(outOfRange: true)
            ->surface($this->component())
            ->guardOverflow('page beyond the content graph, out of range')
            ->shared(self::key());

        self::assertSame(Cacheability::Uncacheable, $plan->cacheability);
        self::assertSame(FragmentScope::Never, $plan->fragmentScope);
        self::assertSame('page beyond the content graph, out of range', $plan->reason);
        self::assertNull($plan->key);
        self::assertInstanceOf(PlanFixtureComponent::class, $plan->surface, 'the same Surface is rendered either way, uncached.');
    }

    public function testTheSurfaceIsBuiltOncePerArmEvenThroughAGuard(): void
    {
        $this->builds = 0;

        $this->builder(outOfRange: true)
            ->surface($this->component())
            ->guardOverflow('beyond the content graph')
            ->shared(self::key());

        self::assertSame(1, $this->builds, 'the closure is built by the terminal, so one arm builds one Surface.');
    }

    public function testTheSurfaceIsNotBuiltUntilATerminal(): void
    {
        $this->builds = 0;

        $this->builder()->surface($this->component());

        self::assertSame(0, $this->builds, 'naming the Surface does not build it.');
    }

    public function testEveryProducedPlanDeclaresItsPairWithNoDefault(): void
    {
        $arms = [
            $this->builder()->surface($this->component())->shared(self::key()),
            $this->builder()->surface($this->component())->uncacheable('a statement about the present moment'),
            $this->builder(outOfRange: true)->surface($this->component())->guardOverflow('beyond the content graph')->shared(self::key()),
        ];

        foreach ($arms as $plan) {
            self::assertInstanceOf(Cacheability::class, $plan->cacheability);
            self::assertInstanceOf(FragmentScope::class, $plan->fragmentScope);

            if (Cacheability::Uncacheable === $plan->cacheability) {
                self::assertNotSame('', $plan->reason, 'an Uncacheable arm states its reason.');
            }
        }
    }

    private function builder(bool $outOfRange = false): SurfacePlanBuilder
    {
        return new SurfacePlanBuilder(
            self::ctx($outOfRange),
            new FragmentCache(),
        );
    }

    /** @return \Closure(): Component */
    private function component(): \Closure
    {
        return function (): Component {
            ++$this->builds;

            return new PlanFixtureComponent();
        };
    }

    private static function key(): FragmentKey
    {
        return FragmentKey::fromParts(PlanFixtureComponent::class, 1, 'en_US');
    }

    private static function ctx(bool $outOfRange): QueryContext
    {
        return new QueryContext(
            kind: QueryKind::Home,
            postType: 'post',
            objectId: null,
            objectSubtype: null,
            queryVars: ['paged' => $outOfRange ? 999 : 1],
            site: new SiteProfile(
                name: 'Test',
                description: 'Test site',
                locale: 'en_US',
                scheme: 'https',
                isRtl: false,
            ),
            outOfRangePage: $outOfRange,
        );
    }
}

final class PlanFixtureComponent implements Component
{
    public function render(): string
    {
        return '<p>fixture</p>';
    }
}
