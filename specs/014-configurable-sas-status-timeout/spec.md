# Feature 014 — Configurable SAS Status Timeout

## Goal

Allow every independent Tahseel Backend deployment to select a safe SAS status HTTP timeout through its private environment configuration without editing tracked source files.

## Problem

The shared Backend currently hard-codes a four-second SAS status timeout. Some valid SAS deployments return the all-users status payload more slowly; one measured deployment required approximately 13.5 seconds for 1,119 users. A local source edit to increase the timeout works but leaves the deployed `production` checkout dirty and can be lost or conflict during future Git updates.

## Required behavior

- Add `SAS4_STATUS_TIMEOUT_SECONDS` as a documented deployment setting.
- Preserve `4` seconds as the default when the variable is absent, so existing deployments do not change behavior.
- Accept deployment values only as whole seconds within the supported range `1..20`, matching the Mobile receive-timeout ceiling.
- Invalid, blank, zero, negative, fractional, or out-of-range configuration falls back safely to the default of `4`; it must never create an unbounded request.
- `ClientSasStatusService` uses the configured value whenever no explicit timeout is injected.
- Explicit constructor timeout injection remains supported for deterministic tests; constructor values are bounded by the same `1..20` rules as deployment configuration.
- After changing `SAS4_STATUS_TIMEOUT_SECONDS`, the deployment must clear/rebuild its Laravel configuration cache so the loaded value matches `.env`.
- The selected timeout continues to apply to both SAS token acquisition and the all-users search.
- Existing exact `sas_username` matching, bounded single retry, success-only cache, status semantics, response shape, authorization, and privacy behavior remain unchanged.

## Deployment examples

- Existing/fast server: omit the variable or use `SAS4_STATUS_TIMEOUT_SECONDS=4`.
- Measured slower server: use `SAS4_STATUS_TIMEOUT_SECONDS=20` in private `.env`.
- A future deployment chooses a measured value within `1..20` without modifying Git-tracked source.
- If a measured SAS request cannot finish within 20 seconds, the deployment is blocked pending SAS query optimization; operators must not keep increasing timeouts beyond the Mobile ceiling.

## Non-goals

- No SAS query redesign or pagination change.
- No Mobile/APK change.
- No customer, invoice, payment, permission, WhatsApp, database, or SAS data mutation.
- No credentials or tokens committed or logged.
- No automatic inference of timeout from user count or prior latency.

## Acceptance gates

- Focused tests prove RED before implementation and GREEN afterward.
- Default remains exactly `4`.
- Valid environment-backed configuration such as `20` reaches the SAS service call.
- Invalid/boundary cases are deterministic and bounded.
- Existing SAS service and endpoint suites pass.
- Full relevant Backend regression passes.
- `.env.example` documents only the non-secret timeout variable.
- Independent read-only audit returns PASS.
- CI passes on the exact feature commit before merge to `production`.
