# ADR-0003 — Shared configuration, and a lockfile resolved through the uncommitted path repository

Status: accepted

## Context

The contribution contract requires every repository to reference the analyzer
configuration and architecture rules shipped by `mahout-devtools`, never to copy
them. It also forbids a committed `path` repository, while `mahout-devtools` is
not published, so the committed manifest cannot resolve it from a published lane
yet the divergence gate requires the committed `composer.lock` to pin it.

## Decision

- `phpstan.neon`, `psalm.xml`, `rector.php` and `.php-cs-fixer.dist.php`
  reference the pinned `mahout-devtools` configuration. None carries a copy.
- `composer stan` and `composer arch` run the same shared PHPStan config,
  because a consumer's root config must include the shared one and the shared one
  already carries the rules.
- `composer.dev.json` (git-ignored) adds path repositories for
  `../mahout-kernel` and `../mahout-devtools` with `symlink: true` and requires
  them at `@dev`. `extra.branch-alias` maps `dev-main` to `1.0.x-dev`, so
  `@dev` and the committed `^1.0` name the same major.
- The committed `composer.lock` is the same resolution as `composer.dev.lock`.

## Consequences

- The committed lock names a path distribution until the packages are published.
  At publication the lock is regenerated from the VCS lane and this ADR is
  superseded.
- `composer install` in a clean clone of a published release uses the published
  lane; until then a contributor runs
  `COMPOSER=composer.dev.json composer install`.
