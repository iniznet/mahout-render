# Changelog

All notable changes to this package are recorded here, in Keep a Changelog
order. The format follows Semantic Versioning; a major entry names each removal.

## [Unreleased]

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
