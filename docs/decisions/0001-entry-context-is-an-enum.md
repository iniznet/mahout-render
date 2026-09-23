# ADR-0001 — The entry context is an enum, and configuration is a service

Status: accepted

## Context

An entry must state where it is served: the front end, the admin, or the block
editor. Three designs were available:

1. Three provider methods, one per context, each with its own action.
2. A boolean flag per entry.
3. One closed enum, with the mapping to the core hook on the enum itself.

The kernel instantiates a provider by class name with no constructor arguments,
so a provider cannot receive configuration directly. The configuration therefore
has to arrive through the container, which the composition root populates before
`boot()`.

## Decision

- `EntryContext` is a string-backed enum with `Front`, `Admin` and `Editor`
  cases and a `hook()` method returning the core enqueue hook constant.
  `EntryContext::cases()` is the closed set the provider iterates to attach one
  action per context. A test asserts each case's hook.
- An entry declaration carries the context as a string, resolved through
  `EntryContext::tryFrom()`. A declaration without a context throws
  `EntryContextMissing`; one that names an unknown context throws
  `EntryContextUnknown`. There is no default and no inference, mirroring the
  field layer's `StorageTarget` rule.
- `AssetsProvider` resolves an `AssetsConfig` from the container, builds the
  `EntryEnqueuer` from it, and registers both. The composition root declares the
  config; the dependency stays explicit and traceable.

## Consequences

- One mapping, one place. Adding a context is one enum case, one `hook()` arm,
  one test row, and one documentation row.
- The provider is stateless beyond the container, so the kernel's no-argument
  instantiation works without a service locator or reflection.
