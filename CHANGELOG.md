# Changelog

All notable changes to this package are recorded here, in Keep a Changelog
order. The format follows Semantic Versioning; a major entry names each removal.

## [Unreleased]

### Changed

- The generated hook reference is now two documents — `docs/reference/actions.md` and
  `docs/reference/filters.md`, replacing `docs/reference/hooks.md`. A single mixed table
  asked the reader to filter rows for the question they actually came with, which hooks
  fire and forget versus which hooks return a value, and that distinction is already
  recorded on every constant's docblock. `composer hooks:check` gates both files, and a
  package that declares none of one kind still carries the other document, so the gate
  cannot quietly stop running. Adopted from `iniznet/mahout-devtools` 2.0.1, whose
  `hooks:check` and `hooks:generate` take `--outdir=docs/reference`; the canonical command
  text lives in that package's gate manifest, and this repository's scripts are compared
  against it by `composer config:check`.

### Added

- The build-manifest reader: `Manifest`, `ManifestEntry`, `ResolvedEntry` and
  `ResolvedAsset`, resolving every declared entry to a URL and a version.
- The entry context: `EntryContext` and `EntryPoint`, with a required context
  and no default, and `EntryEnqueuer` registering through the Script Modules
  API.
- The `AssetsProvider` service provider and the `AssetsConfig` it resolves.
- The public `Contracts` surface: `ManifestSource` and `AssetSize`.
- The `mahout/assets/entries` filter and the `mahout/assets/before_enqueue`,
  `mahout/assets/registered` and `mahout/assets/manifest_missing` actions.
- The build-time asset size gate: `AssetBudget`, `BudgetCheck`, `BudgetVerdict`
  and `BudgetBreach`, run by `composer budget:check`.
- The development-mode switch, read from the single `MAHOUT_ASSETS_DEV`
  constant, with no network probe.
- The architecture-rule proof fixture for a raw hook name.
