# ADR-0002 — The manifest and asset sizes are read behind contracts

Status: accepted

## Context

The package must prove two behaviours that are otherwise awkward to test:
a missing manifest fails loudly in development and records a warning and serves
nothing in production, and the build-time size check fails on an over-budget
build. Both read the filesystem. A class that calls `file_get_contents()`
directly can only be tested against real files, and the missing-manifest branch
can only be exercised by deleting a file.

## Decision

- `Contracts\ManifestSource` supplies `path()`, `readable()` and
  `contents()`. `Internal\FileManifestSource` is the production
  implementation; a test fixture makes the source unreadable at will.
- `Contracts\AssetSize` supplies `bytes()`. `Internal\GzipFileSize` is the
  production implementation, reading a build root and gzipping at level 9.
- `Manifest` parses JSON only; it names no WordPress function and touches no
  filesystem. `EntryEnqueuer` is the only class that names a WordPress function.

## Consequences

- The missing-manifest and over-budget branches are provable without a build.
- An unreadable file is one failure (`FileUnreadable`), raised by both
  implementations, so the enqueuer and the budget check fail the same way.
