# Extending

## A new dispatch arm

An arm is one `match (true)` case in the host's table, built with
`SurfacePlanBuilder` and finished at one terminal:

- `->shared(FragmentKey::fromParts([...]))` — cacheable against a
  content-derived key;
- `->uncacheable($reason)` — stores nothing, states why;
- `->guardOverflow($reason)->shared($key)` — the listing arm's second path:
  the same Surface beyond the last page the content graph holds;
- `SurfacePlan::wrapped($class, $scope)` — a plan whose cacheability is
  neither Shared pair.

The suite's dispatch audit fails on an arm that reaches no terminal, and on an
`Uncacheable` arm with no reason.

## A new component

Implement `Component` (`render(): string`), or extend `MarkupComponent`
for the class-resolved markup-file shape: one markup file, one bound variable
set, `ob_start()`, no `extract()`, exactly one escape per output. A
component fetches nothing, touches no global and fires no hook; data arrives
as constructor-typed props.

## A new fragment key part

Every part must be enumerable from the site's own content graph — a post id, a
term id, a page number bounded by the graph. A part derived from anything a
visitor can supply invents a cache a visitor can fill, and fails the review.

## A response-header change

Headers are the `Cache\HeaderPolicy`'s to emit, and nothing else's. A new
header is a change to the policy plus a test that two anonymous visitors stay
byte-identical and that the policy survives an uncacheable arm.
