# Getting started

## Install

```bash
composer require iniznet/mahout-render:^1.0
```

Requires PHP 8.4 and WordPress 7.1 or later, and depends on
`iniznet/mahout-kernel`.

## First Surface

A Surface is a `Component`; a page is one arm in the host's dispatch table.
The smallest complete example — a page whose fragment cache key derives from
the post id:

```php
use Iniznet\Mahout\Render\Component;
use Iniznet\Mahout\Render\FragmentKey;
use Iniznet\Mahout\Render\MarkupComponent;
use Iniznet\Mahout\Render\QueryContext;

final class HelloPage extends MarkupComponent
{
    public function __construct(private readonly QueryContext $ctx) {}

    #[\Override]
    public function render(): string
    {
        \ob_start();
        $title = 'Hello';
        require __DIR__.'/markup/hello.php';

        return (string) \ob_get_clean();
    }
}

// in the dispatch table:
$builder->surface(static fn (): Component => new HelloPage($ctx))
    ->shared(FragmentKey::fromParts(['hello', (string) $ctx->objectId]));
```

Three facts to internalise:

1. **The arm declares the cacheability.** `shared()`, `uncacheable($reason)`
   and `guardOverflow($reason)->shared()` are the terminals; an arm that
   reaches none fails the suite. A per-request plan names its class and scope
   with `SurfacePlan::wrapped()`.
2. **The key is content-derived.** Every `FragmentKey` part must be enumerable
   from the site's own content graph — a key an anonymous visitor can invent
   is a cache they can fill.
3. **A throwing Surface is a defined render.** Production renders
   `ErrorSurface` at 500; development rethrows. A Surface owes no try/catch.

## Failure modes

| Symptom | Cause |
|---|---|
| `UncacheableWithoutReason` | an `uncacheable()` arm carried no reason |
| `OverflowGuardWithoutReason` | `guardOverflow()` without a reason |
| `EmptyKeyPart` | a `FragmentKey::fromParts()` entry was empty |
| `SurfaceDataMissing` | a singular request whose object resolved to nothing |
