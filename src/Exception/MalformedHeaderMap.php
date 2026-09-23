<?php

/**
 * A header map that is not the map `wp_headers` documents.
 *
 * The documented payload is `array<string, string|false>`. An entry keyed by an
 * integer offset, or carrying a value that is neither a string nor `false`,
 * cannot reach the wire: a name would be invented from the offset and a value
 * would be coerced into a string, and either is a second answer to a question
 * the policy already answered. The request is refused instead.
 */

declare(strict_types=1);

namespace Iniznet\Mahout\Render\Exception;

final class MalformedHeaderMap extends \UnexpectedValueException implements RenderException
{
    private function __construct(
        string $message,
        private readonly string|int $offset,
    ) {
        parent::__construct($message);
    }

    public static function keyedByOffset(int $offset): self
    {
        return new self(
            \sprintf('The header map carries an entry at integer offset %d; every entry is keyed by its header name.', $offset),
            $offset,
        );
    }

    public static function valuedByNothing(string $name): self
    {
        return new self(
            \sprintf('The "%s" header carries a value that is neither a string nor the false that removes it.', $name),
            $name,
        );
    }

    public function offset(): string|int
    {
        return $this->offset;
    }
}
