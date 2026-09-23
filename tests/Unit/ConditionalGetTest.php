<?php

/**
 * The conditional GET at the origin: when a validator exists, what it is, and
 * when a `304` is the answer.
 *
 * The `304` is asserted through core's own `status_header` filter rather than
 * through `headers_sent()`, because the CLI SAPI accepts a header call and
 * discards it: the filter is the observable fact that the status was set.
 */

declare(strict_types=1);

namespace Iniznet\Mahout\Render\Tests;

use Iniznet\Mahout\Render\Cache\ConditionalGet;
use Iniznet\Mahout\Render\Cache\HeaderPolicy;
use Iniznet\Mahout\Render\Cacheability;
use Iniznet\Mahout\Render\FragmentKey;
use PHPUnit\Framework\TestCase;

final class ConditionalGetTest extends TestCase
{
    private const string KEY_PART = 'Iniznet\\Mahout\\Render\\Tests\\ConditionalGetSurface';

    protected function tearDown(): void
    {
        \remove_all_filters('status_header');

        parent::tearDown();
    }

    public function testASharedResponseWithAStoredFragmentCarriesAValidator(): void
    {
        $validator = $this->conditionalGet(Cacheability::Shared, $this->key(), null)->validator();

        self::assertNotNull($validator);
        self::assertStringStartsWith('"', $validator);
        self::assertStringEndsWith('"', $validator);
        self::assertSame('"'.\md5($this->key()->toString()).'"', $validator, 'the validator is the key digest, quoted.');
    }

    public function testTwoVisitorsOnOneKeyReceiveOneValidator(): void
    {
        $first = $this->conditionalGet(Cacheability::Shared, $this->key(), null)->validator();
        $second = $this->conditionalGet(Cacheability::Shared, $this->key(), null)->validator();

        self::assertSame($first, $second, 'the validator is a function of the key and never of the body or the visitor.');
    }

    public function testAnUncacheableResponseStatesNoValidator(): void
    {
        self::assertNull($this->conditionalGet(Cacheability::Uncacheable, $this->key(), null)->validator());
    }

    public function testAPlanThatWrapsNoFragmentStatesNoValidator(): void
    {
        self::assertNull($this->conditionalGet(Cacheability::Shared, null, null)->validator());
    }

    public function testARequestHoldingTheCurrentValidatorIsAnsweredThreeZeroFour(): void
    {
        $conditional = $this->conditionalGet(Cacheability::Shared, $this->key(), $this->etag());

        self::captureStatusHeader($code);
        self::assertTrue($conditional->answerNotModified(), 'the request already holds this response.');
        self::assertSame(304, $code, 'the status is set before the caller stops.');
    }

    public function testARequestHoldingAStaleValidatorIsAnsweredInFull(): void
    {
        self::captureStatusHeader($code);

        self::assertFalse($this->conditionalGet(Cacheability::Shared, $this->key(), '"deadbeefdeadbeefdeadbeefdeadbeef"')->answerNotModified());
        self::assertNull($code, 'no status is set when the validator does not match.');
    }

    public function testARequestWithNoValidatorHeaderIsAnsweredInFull(): void
    {
        self::captureStatusHeader($code);

        self::assertFalse($this->conditionalGet(Cacheability::Shared, $this->key(), null)->answerNotModified());
        self::assertNull($code);
    }

    public function testAConditionalRequestAgainstAnUncacheableResponseIsAnsweredInFull(): void
    {
        self::captureStatusHeader($code);

        self::assertFalse($this->conditionalGet(Cacheability::Uncacheable, $this->key(), $this->etag())->answerNotModified());
        self::assertNull($code, 'the honest answer to a conditional request against a response that is not stored is a full 200.');
    }

    public function testAPrivateResponseCarriesAValidatorAndAnswersThreeZeroFour(): void
    {
        $conditional = $this->conditionalGet(Cacheability::Private, $this->key(), $this->etag());

        self::assertNotNull($conditional->validator());
        self::captureStatusHeader($code);
        self::assertTrue($conditional->answerNotModified());
        self::assertSame(304, $code);
    }

    private function conditionalGet(Cacheability $class, ?FragmentKey $key, ?string $ifNoneMatch): ConditionalGet
    {
        return new ConditionalGet(
            HeaderPolicy::derive($class, 'GET', false),
            $key,
            $ifNoneMatch,
        );
    }

    private function key(): FragmentKey
    {
        return FragmentKey::fromParts(self::KEY_PART, 7, 1, 'en_US');
    }

    private function etag(): string
    {
        return $this->conditionalGet(Cacheability::Shared, $this->key(), null)->validator() ?? '';
    }

    /**
     * Watch core's own status header. The captured code stays null while no
     * status is set.
     *
     * @param int|null $code captured by reference
     */
    private function captureStatusHeader(?int &$code): void
    {
        $code = null;

        \add_filter(
            'status_header',
            static function (string $statusHeader, int $statusCode) use (&$code): string {
                $code = $statusCode;

                return $statusHeader;
            },
            10,
            2,
        );
    }
}
