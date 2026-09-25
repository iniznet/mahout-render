# ADR-0002 — The error boundary is its own hook seam

Status: accepted

## Context

Hooks are emitted from Providers and Modules. The surface failure is not a
registration or a boot event: it happens inside the render, after the
Diagnostics record exists and before the defined error page is chosen. Moving
the emission outward — to the provider that attached the boundary — would put
the notice after the render instead of before it, and would hand the
`Throwable` across a seam that has already produced its substitute.

## Decision

`SurfaceErrorBoundary` — the one class that holds both the failure and the
reference — emits `mahout/render/surface_failed` itself, with the `Throwable`
and the support reference. The hook observes; it may not alter the outcome.
Every other hook in the package is emitted from a Provider or the registry as
the contract states.

## Consequences

A listener sees the failure before the defined page renders, in production
and in development alike, and the emission site is one line in one class —
greppable like every other emission.