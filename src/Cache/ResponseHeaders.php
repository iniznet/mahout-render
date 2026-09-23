<?php

/**
 * The header map a response carries: core's own, narrowed at the boundary, and
 * a declared policy merged onto it.
 *
 * `WP::send_headers()` documents its filter payload as
 * `array<string, string|false>`, and `false` is core's own convention for
 * removing a header it set. Nothing else in that shape is a header statement, so
 * an entry keyed by an integer offset or carrying an unrepresentable value is
 * refused rather than coerced — coercion would invent a header name or turn a
 * structured value into a string, and either is a second answer to a question
 * the policy has already answered.
 *
 * The merge order is the contract: core's map first, the declared statement
 * last, so exactly one `Cache-Control` and one `Vary` survive and no subscriber
 * can widen a response that the cacheability class bounded.
 */

declare(strict_types=1);

namespace Iniznet\Mahout\Render\Cache;

use Iniznet\Mahout\Render\Exception\MalformedHeaderMap;

final readonly class ResponseHeaders
{
    private function __construct()
    {
    }

    /**
     * Core's payload, narrowed to the documented shape.
     *
     * @param array<array-key, mixed> $payload
     *
     * @return array<string, string|false>
     */
    public static function narrow(array $payload): array
    {
        $narrowed = [];

        foreach ($payload as $name => $value) {
            if (!\is_string($name)) {
                throw MalformedHeaderMap::keyedByOffset($name);
            }

            if (!\is_string($value) && false !== $value) {
                throw MalformedHeaderMap::valuedByNothing($name);
            }

            $narrowed[$name] = $value;
        }

        return $narrowed;
    }

    /**
     * A narrowed map with the declared policy merged over it, the policy last.
     *
     * @param array<string, string|false> $narrowed
     *
     * @return array<string, string|false>
     */
    public static function merge(array $narrowed, HeaderPolicy $policy): array
    {
        return [...$narrowed, ...$policy->headers()];
    }
}
