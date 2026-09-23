# mahout-assets

## What it is

The build-manifest reader that turns a Vite build into WordPress script modules
and stylesheets, with an explicit entry context. It owns asset resolution, the
build manifest and the front, admin and editor entry contexts; it owns no theme,
no content type and no storage.

## Installation

There is no Packagist lane. Consume the repository over VCS and pin the major:

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/iniznet/mahout-assets.git" }
    ],
    "require": {
        "iniznet/mahout-assets": "^1.0"
    }
}
```

```bash
composer require iniznet/mahout-assets:^1.0
```

A development checkout points at sibling directories through an uncommitted
`composer.dev.json` (path repositories plus `@dev`) and runs
`COMPOSER=composer.dev.json composer install`.

## The public `Contracts/` surface

`src/Contracts/` is the package's entire public API. Everything under
`src/Internal/` is `@internal` and may change in a patch release.

| Interface | Role | Implementations |
|---|---|---|
| `ManifestSource` | where the build manifest lives: `path()`, `readable()`, `contents()` | `Internal\FileManifestSource` |
| `AssetSize` | the transfer size of one built asset: `bytes()` | `Internal\GzipFileSize` |

## Minimal usage

The composition root declares an `AssetsConfig`, names `AssetsProvider`, and
boots the kernel. Nothing is registered at file scope and nothing is read from
the filesystem at construction.

```php
<?php

declare(strict_types=1);

use Iniznet\Mahout\Assets\AssetsConfig;
use Iniznet\Mahout\Assets\AssetsProvider;
use Iniznet\Mahout\Assets\DevMode;
use Iniznet\Mahout\Assets\EntryList;
use Iniznet\Mahout\Assets\Internal\FileManifestSource;
use Iniznet\Mahout\Kernel\Kernel;

$kernel = Kernel::inWordPress();

$kernel->service(new AssetsConfig(
    manifest: new FileManifestSource(get_theme_file_path('build/manifest.json')),
    entries: EntryList::fromDeclarations([
        [
            'handle' => 'howdah-app',
            'source' => 'src/front.ts',
            'context' => 'front',
            'dependencies' => ['@wordpress/interactivity'],
            'domain' => 'howdah',
            'translations_path' => get_theme_file_path('languages'),
        ],
    ]),
    baseUrl: get_theme_file_uri('build'),
    devMode: DevMode::fromConstant(),
));

$kernel->provider(AssetsProvider::class);
$kernel->boot();
```

An entry is enqueued by the context's core hook: `wp_enqueue_scripts` for
`front`, `admin_enqueue_scripts` for `admin`, `enqueue_block_editor_assets`
for `editor`. An entry that names no context is refused; there is no default.

### Development mode

Development mode is one explicit constant. Define it while developing:

```php
define('MAHOUT_ASSETS_DEV', true);
```

There is no network probe. When the constant is defined and true, a missing
manifest throws; otherwise a missing manifest records a `warning` through
`Diagnostics`, fires `mahout/assets/manifest_missing` and serves nothing.

### The build-time size gate

```bash
composer budget:check
```

It measures every manifest entry's gzipped CSS and JavaScript against the
per-route ceilings (50 KB and 60 KB by default) and exits non-zero on any entry
over its ceiling.

## Documented public concrete classes

Every documented public class is part of the stable surface within a major.

| Class | Role |
|---|---|
| `AssetsProvider` | the `ServiceProvider` that builds the enqueuer and attaches one action per context |
| `AssetsConfig` | the manifest source, the entry list, the base URL and the dev-mode switch |
| `EntryList` | the one explicit entry list, and the entries for a context |
| `EntryPoint` | one declared entry: handle, manifest source, context, dependencies, translations |
| `EntryContext` | the `Front` / `Admin` / `Editor` enum and the core hook each maps to |
| `Manifest` | the parsed build manifest, resolving an entry to a URL and a version |
| `ManifestEntry` | one parsed manifest record |
| `ResolvedEntry` | one entry bound to its built module and stylesheets |
| `ResolvedAsset` | one built asset's handle, file, URL and version |
| `ScriptModuleTranslations` | the text domain and directory a module needs |
| `DevMode` | the one development-mode switch, read from `MAHOUT_ASSETS_DEV` |
| `AssetBudget` | the per-route CSS and JavaScript ceilings |
| `BudgetCheck` | the build-time size check |
| `BudgetVerdict` | the check's result: the breaches, or none |
| `BudgetBreach` | one measured asset over its ceiling |
| `Hooks` | every hook constant the package emits or observes |

The package filters `mahout/assets/entries` and fires
`mahout/assets/before_enqueue`, `mahout/assets/registered` and
`mahout/assets/manifest_missing`. The generated reference is
`docs/reference/hooks.md`.

## Compatibility

| Item | Value |
|---|---|
| PHP | 8.4 or later |
| WordPress | 7.1 or later |
| `Contracts/` | stable within a major version; a change is a contract change and is published as a major |
| `Internal/` | unguaranteed; may change in a patch release |
| Licence | GPL-2.0-or-later |

## Architecture

The package is framework-blind between the manifest and WordPress. `Manifest`
parses JSON and computes URLs and versions; `EntryEnqueuer` is the only class
that names a WordPress function. Every script module is registered through
`wp_register_script_module()` and enqueued through `wp_enqueue_script_module()`;
stylesheets are enqueued through `wp_enqueue_style()`. There is no
`script_loader_tag` filter and no inline script or style.

## Licence

GPL-2.0-or-later. See [LICENSE](./LICENSE).
