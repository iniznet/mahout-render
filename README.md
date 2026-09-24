# mahout-render

## What it is

The render pipeline: a request's query context resolved to one Surface plan,
the plan built through a builder whose every arm declares its cacheability,
typed components rendered to HTML, fragments cached against content-derived
keys, and the error boundary that turns a throwing Surface into a defined
render. It owns resolution, the document shell, fragment caching and the
response-header policy; it owns no content type, no field and no markup
library.

## Installation

There is no Packagist lane. Consume the repository over VCS and pin the major:

```bash
composer require iniznet/mahout-render:^1.0
```

A development checkout points at sibling directories through an uncommitted
`composer.dev.json` (path repositories plus `@dev`) and runs
`COMPOSER=composer.dev.json composer install`.

## The public `Contracts/` surface

`src/Contracts/` is the package's entire public API. Everything under
`src/Internal/` is `@internal` and may change in a patch release.

## The one path a request takes

1. `QueryContext::current()` resolves the request's facts: the `QueryKind`,
   the queried object id, the page var, the `SiteProfile`.
2. The host's dispatch table matches the context to a plan with
   `SurfacePlanBuilder`. An arm declares a Surface and one terminal — there is
   no default and no terminal-less path:

```php
$plan = match (true) {
    QueryKind::Embed === $ctx->kind => $builder
        ->surface(static fn (): Component => new EmbedContent($ctx, $content))
        ->uncacheable('embed document, rendered for one parent request'),
    QueryKind::Singular === $ctx->kind => $builder
        ->surface(static fn (): Component => new SinglePost($ctx, $content))
        ->shared(FragmentKey::fromParts(['post', (string) $ctx->objectId])),
    // …
};

// A listing arm past the content graph's last page is the same Surface,
// uncacheable and stated:
$builder->guardOverflow('beyond the last page the content graph holds')
    ->shared(self::indexKey($ctx));
```

3. `shared($key)` is the `Cacheability::Shared` over `FragmentScope::Shared`
   pair; `uncacheable($reason)` stores nothing and must say why;
   `SurfacePlan::wrapped()` names class and scope for a plan that is neither.
4. `Bootstrap::render()`-shaped hosts read the memoised plan, wrap the Surface
   in the `SurfaceErrorBoundary`, and render.

## Components and the document shell

A component implements `Component`: `render(): string`. Two bases cover the
common shapes — `MarkupComponent` for a class-resolved markup file, and plain
composition components that return other components. `Document` is the page
shell (header, main, footer); `Stack` composes children; `SiteProfile` carries
the site's identity facts. `wp_head` and `wp_footer` fire inside the
`Document` component and are never removed.

## Fragment caching

`FragmentCache` stores a rendered Surface under its `FragmentKey`. A key's
parts must be enumerable from the site's own content graph — an anonymous
visitor can invent nothing. `Cache\HeaderPolicy` and `Cache\ConditionalGet`
emit the one `Cache-Control`/`Vary`/validator policy the pipeline owns; no
other component may set those headers.

## Failures are defined renders

A Surface that throws is recorded through `Diagnostics` and rendered by
`ErrorSurface` with status `500` in production; development rethrows. There is
no white screen and no degraded mode — the error page is a defined render.

## Compatibility

| Item | Value |
|---|---|
| PHP | 8.4 or later |
| WordPress | 7.1 or later |
| `Contracts/` | stable within a major version |
| Licence | GPL-2.0-or-later |

The decisions this package made are recorded under `docs/decisions/`; the
discipline contract is `AGENTS.md`.

## Licence

GPL-2.0-or-later. The full text is in [LICENSE](./LICENSE).
