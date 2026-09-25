<?php

/**
 * The shells and the error surface render their defined output: one head, one
 * skip link, one main landmark, the host's chrome in its slots, the two core
 * hooks once each, and the embed shell without chrome.
 */

declare(strict_types=1);

namespace Iniznet\Mahout\Render\Tests;

use PHPUnit\Framework\TestCase;

final class ShellTest extends TestCase
{
    protected function tearDown(): void
    {
        \remove_all_actions('wp_head');
        \remove_all_actions('wp_footer');
        \remove_all_actions('wp_body_open');

        parent::tearDown();
    }

    public function testTheDocumentShellRendersItsSlotsAndFiresTheTwoHooksOnceEach(): void
    {
        $head = 0;
        $footer = 0;
        \add_action('wp_head', static function () use (&$head): void { ++$head; });
        \add_action('wp_footer', static function () use (&$footer): void { ++$footer; });

        $html = (new \Iniznet\Mahout\Render\Document(
            self::resolver(),
            main: self::byte('MAIN'),
            header: self::byte('HEADER'),
            footer: self::byte('FOOTER'),
        ))->render();

        self::assertStringContainsString('Skip to content', $html, 'the skip link renders.');
        self::assertSame(1, $head, 'wp_head fires once, inside the shell.');
        self::assertSame(1, $footer, 'wp_footer fires once, inside the shell.');

        // The order is the document's: header, main, footer.
        self::assertGreaterThan(\strpos($html, 'HEADER'), \strpos($html, 'MAIN'), 'the main landmark follows the header.');
        self::assertGreaterThan(\strpos($html, 'MAIN'), \strpos($html, 'FOOTER'), 'the footer follows the main landmark.');
    }

    public function testTheShellHalvesSplitAtTheMainLandmark(): void
    {
        $document = new \Iniznet\Mahout\Render\Document(self::resolver(), main: self::byte('MAIN'));

        $opening = $document->opening();
        $closing = $document->closing();

        self::assertStringContainsString('<head>', $opening);
        self::assertStringContainsString('id="main"', $opening, 'the opening half ends at the main landmark.');
        self::assertStringContainsString('</main>', $closing, 'the closing half closes the landmark.');
        self::assertSame($opening.$closing, $document->render(), 'the two halves are the same markup the shims include.');
    }

    public function testTheEmbedShellRendersTheContentWithoutChrome(): void
    {
        $head = 0;
        $footer = 0;
        \add_action('wp_head', static function () use (&$head): void { ++$head; });
        \add_action('wp_footer', static function () use (&$footer): void { ++$footer; });

        $html = (new \Iniznet\Mahout\Render\EmbedDocument(self::resolver(), self::byte('EMBEDDED')))->render();

        self::assertStringContainsString('EMBEDDED', $html, 'the content component renders.');
        self::assertSame(1, $head, 'wp_head fires once.');
        self::assertSame(1, $footer, 'wp_footer fires once.');
        self::assertStringNotContainsString('Skip to content', $html, 'no navigation renders in the embed document.');
    }

    public function testTheErrorSurfaceRendersTheReferenceEscapedOnce(): void
    {
        $html = (new \Iniznet\Mahout\Render\ErrorSurface(self::resolver(), 'MH-1<b>'))->render();

        self::assertStringContainsString('Something went wrong.', $html, 'the defined copy renders.');
        self::assertStringContainsString('MH-1&lt;b&gt;', $html, 'the reference is escaped exactly once.');
        self::assertStringNotContainsString('MH-1<b>', $html, 'no raw reference byte reaches the page.');
    }

    private static function resolver(): \Iniznet\Mahout\Render\ClassNameResolver
    {
        return new class implements \Iniznet\Mahout\Render\ClassNameResolver {
            public function resolve(string $semantic): string
            {
                return $semantic;
            }

            public function __invoke(string $semantic): string
            {
                return $this->resolve($semantic);
            }
        };
    }

    private static function byte(string $bytes): \Iniznet\Mahout\Render\Component
    {
        return new class($bytes) implements \Iniznet\Mahout\Render\Component {
            public function __construct(private readonly string $bytes)
            {
            }

            public function render(): string
            {
                return $this->bytes;
            }
        };
    }
}
