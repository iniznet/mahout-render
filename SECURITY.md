# Security policy

## The private route

Report a vulnerability through GitHub's private vulnerability reporting on this
repository (the *Security* tab, then *Report a vulnerability*). No security email
address is published.

## Response expectation

| Stage | Commitment |
|---|---|
| Acknowledgement | within 3 working days |
| Initial assessment | within 10 working days |
| Fix or a written plan | a defect in a repository-owned surface gets a fix or a dated plan |
| Disclosure | coordinated with the reporter, after the fix or after 90 days, whichever comes first |
| Credit | offered, never assumed |

## What is in scope in this repository

- The build manifest parser and the URL and version it derives.
- The entry context and the hook each context attaches to.
- The Script Modules API registration and the translations call.
- The build-time size gate.

A defect in a different repository of the family belongs in that repository.
