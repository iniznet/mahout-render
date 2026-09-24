<?php

declare(strict_types=1);

namespace Iniznet\Mahout\Render;

/**
 * The document shell: one head, one skip link, one header, one main landmark,
 * one footer. wp_head and wp_footer fire here and nowhere else. The opening
 * and closing halves are the same markup the header.php and footer.php shims
 * include, so a legacy caller cannot render a different shell.
 *
 * The header and footer slots take the host's site chrome: the shell owns
 * placement, the host owns identity. The document-title heading is gone —
 * each Surface owns its page heading, and the shell's second one made every
 * page carry a duplicate outline entry.
 *
 * The shell is a composite of two markup halves, not a single-markup
 * component, so it does not extend Component.
 */
final readonly class Document
{
    public function __construct(
        private ClassNameResolver $classes,
        private readonly ?Component $main = null,
        private readonly ?Component $header = null,
        private readonly ?Component $footer = null,
    ) {
    }

    public function render(): string
    {
        return $this->opening().$this->closing();
    }

    /** The opening half, for the header.php compatibility shim. */
    public function opening(): string
    {
        return $this->renderMarkup(__DIR__.'/markup/shell-open.php');
    }

    /** The closing half, for the footer.php compatibility shim. */
    public function closing(): string
    {
        return $this->renderMarkup(__DIR__.'/markup/shell-close.php');
    }

    private function renderMarkup(string $path): string
    {
        \ob_start();
        $c = $this->classes;
        $main = $this->main;
        $header = $this->header;
        $footer = $this->footer;
        require $path;

        return (string) \ob_get_clean();
    }
}
