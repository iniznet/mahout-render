# ADR-0001 — The request facts are read once, in one place

Status: accepted

## Context

A render depends on ambient request state: which query is on, which object it
names, the site profile, the pagination. Reading `$wp_query` and the site
globals at each use site scatters the reads across the pipeline, makes the
ambient state invisible in the types, and leaves every component one step from
a global it must not touch.

The static-access contract resolves collaborators statically only at named
boundaries. A request-facts read is such a boundary: it resolves no
collaborator, it reads the one global the request already built.

## Decision

`QueryContext` is the request's facts as a value, and `QueryContext::current()`
is the one `$wp_query` read and the one site read in the codebase. Everything
else in the pipeline receives a `QueryContext` — through the plan, the
boundary, the header policy — and never touches a global. A context read
before the query exists refuses with `NotBooted`.

## Consequences

Every ambient value a render depends on is a property of one typed object, so
a test builds the request it needs instead of mutating a global. A second
request-context read anywhere in the package is a contract breach, and the
architecture rules name `QueryContext::current()` in the boundary exception
list rather than leaving it to prose.
