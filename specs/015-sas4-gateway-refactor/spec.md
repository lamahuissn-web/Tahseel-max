# Feature 015 — Unified SAS4 Gateway Refactor

## Goal

Make every Tahseel admin and mobile SAS4 interaction pass through one trusted application-layer gateway while preserving current user-visible behavior, existing database linkage, and the SAS4 protocol.

## Problem

Tahseel currently has a sound low-level SAS4 client and a hardened mobile batch-status service, but legacy admin controllers call the low-level client directly. Status checks accept browser-supplied usernames and perform per-user lookups, while reads, controls, and client linking each apply their own response and failure rules. This duplication creates inconsistent status semantics, N+1 SAS calls, and a larger security and maintenance surface.

## Required behavior

- Introduce one Laravel SAS4 gateway as the only application-facing entry point for client status, information, traffic, profiles, account search/link/create, and control actions.
- Keep `Sas4ApiService` as the sole protocol transport for AES encryption, authentication, JWT caching, HTTP calls, and raw SAS4 endpoint access.
- Controllers and commands must not call `Sas4ApiService` directly after migration.
- Resolve trusted Tahseel client IDs to the existing exact `tbl_clients.sas_username` linkage on the backend.
- Consolidate admin status onto `ClientSasStatusService`: one bounded all-users request per cache miss, no per-card lookup, exact matching, and explicit `online`, `offline`, `unlinked`, `not_found`, or `unavailable` states.
- Admin status requests accept `client_ids`, not arbitrary usernames, and enforce a list of 1..100 unique positive integer IDs.
- Preserve the JWT-authenticated mobile endpoint contract and active/non-deleted visibility scope.
- Preserve existing client info, traffic, daily traffic, search, profile, link/create/unlink, enable, disable, disconnect, and profile-change user workflows.
- Normalize gateway failures so controllers do not infer meaning from null or malformed raw responses.
- Control operations must validate a closed action set, resolve the exact linked SAS account, execute once, and verify the resulting state when the provider exposes a verifiable field.
- Never blindly retry write/control operations.
- Remove functional credential defaults from tracked configuration. Missing URL/username/password/AES key must fail closed as `not_configured`, never use bundled credentials.
- Never log or return SAS credentials, JWT tokens, raw authorization headers, or rich provider payloads from status endpoints.
- No database schema changes.

## Compatibility

- Existing route names remain available so deployed Blade code and clients are not broken unnecessarily.
- The legacy admin status response may change only together with its tracked Blade callers in the same feature.
- Existing `sas_username` values remain untouched.
- No real SAS account mutation is permitted during automated or live verification.

## Non-goals

- No new server, VM, reverse proxy, or external network gateway.
- No direct SAS database access.
- No automatic name-based linking.
- No SAS4 API redesign, background snapshot table, or queue.
- No HTTPS/firewall deployment changes in this feature.
- No invoice, payment, WhatsApp, Telegram, customer, or permission redesign.

## Acceptance gates

- Every production behavior change is preceded by an observed failing test.
- Direct `Sas4ApiService` use outside the gateway/status service and transport tests is eliminated for migrated app code.
- Admin status performs constant SAS calls between one and the maximum visible batch.
- Invalid IDs/usernames cannot enumerate arbitrary SAS accounts.
- Provider timeout/failure is `unavailable`, never `offline`.
- Control methods execute at most one provider write and never auto-retry it.
- Missing configuration fails closed without network access.
- Existing SAS4 feature tests and relevant full suite pass.
- PHP syntax, routes, and read-only live connection verification pass.
- Independent fresh-context audit returns PASS before commit.
- No commit, push, PR, merge, production branch switch, `.env` edit, or real SAS control action without separate approval.
