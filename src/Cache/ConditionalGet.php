<?php

/**
 * The conditional GET, answered at the origin.
 *
 * Core implements conditional GET for feeds and for nothing else, so a response
 * the theme renders answers it here, in the same shape: a validator derived from
 * the stored fragment, compared against the request's own, and a `304` with no
 * body when they still match.
 *
 * A validator exists only where there is a stored representation to name, so an
 * `Uncacheable` response or a plan that wraps no fragment carries none. The
 * honest answer to a conditional request against a response that is not stored
 * is a full `200`.
 *
 * The `304` is the one WordPress call in this class, and it is bounded: the
 * status header is core's own `status_header()`. Ending the request is not this
 * class's business — `answerNotModified()` reports that the response is complete
 * and the composition root stops there.
 */

declare(strict_types=1);

namespace Iniznet\Mahout\Render\Cache;

use Iniznet\Mahout\Render\FragmentKey;

final readonly class ConditionalGet
{
    public function __construct(
        private HeaderPolicy $policy,
        private ?FragmentKey $key,
        private ?string $ifNoneMatch,
        private int $salt,
    ) {
    }

    /**
     * The response's own validator, or null when it states none.
     *
     * The digest is of the fragment key and of the group's invalidation salt,
     * and never of the body: the key is the enumerable statement of the
     * content graph the bytes came from, the salt is the invalidation state
     * that rotates under it, and together they change exactly when the stored
     * representation can. Naming them needs no output buffering, and two
     * visitors on one key and one salt receive one validator.
     */
    public function validator(): ?string
    {
        if (!$this->policy->emitsValidators() || null === $this->key) {
            return null;
        }

        return '"'.\md5($this->key->toString().'|'.$this->salt).'"';
    }

    /**
     * Whether this request already holds the response. When it does, the `304`
     * is emitted here and true is returned: the response is complete, and the
     * caller must send no body.
     *
     * The header's comparison follows RFC 9110: the field is a comma-separated
     * list, a star matches any representation the origin currently holds, and
     * a weak validator never strong-matches.
     */
    public function answerNotModified(): bool
    {
        $validator = $this->validator();

        if (null === $validator || null === $this->ifNoneMatch) {
            return false;
        }

        if (!$this->matches($validator)) {
            return false;
        }

        \status_header(304);

        return true;
    }

    private function matches(string $validator): bool
    {
        foreach (\explode(',', $this->ifNoneMatch ?? '') as $tag) {
            $tag = \trim($tag);

            if ('*' === $tag) {
                return true;
            }

            if (\str_starts_with($tag, 'W/')) {
                continue;
            }

            if ($tag === $validator) {
                return true;
            }
        }

        return false;
    }
}
