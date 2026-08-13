# Feature Brief

## Identity

- Project: Tahseel Backend
- Repository/worktree: `/root/projects/Tahseel-sas4-gateway-refactor`
- Base branch/commit: `production` / `6c8313e`
- Feature branch: `feature/sas4-gateway-refactor`
- Risk: high — authenticated external ISP account reads and control operations
- Owner approval received for: isolated specification, source/test implementation, local deterministic tests, and read-only live SAS verification

## Outcome

All Tahseel SAS4 features use one trusted application gateway with consistent identity, status, failure, security, and performance behavior.

## Current Behavior

- Mobile API status uses a hardened client-ID batch service.
- Legacy admin status accepts browser usernames and performs per-user lookups.
- Admin reads, controls, link/create flows, and auto-match call the low-level SAS4 client directly.
- Evidence: audited routes/controllers/services and a successful read-only SAS search from the production runtime user.

## Required Behavior

1. Controllers/commands call `Sas4Gateway`; only gateway/status infrastructure calls the raw transport.
2. Status uses scoped Tahseel client IDs, exact `sas_username`, one bounded batch lookup, and closed status semantics.
3. Existing read/link/create/control UX remains compatible.
4. Missing credentials fail closed without network access.
5. Provider writes execute at most once and are never blindly retried.

## Non-Goals

- No server/network gateway, direct SAS database access, migrations, queues, or status snapshot tables.
- No invoice/payment/WhatsApp/Telegram changes.
- No automatic fuzzy identity linking.

## Data and Contract

- Status input: list-shaped `client_ids`, 1..100 unique positive integers.
- Status output: `client_id`, nullable `sas_username`, status in `online|offline|unlinked|not_found|unavailable`.
- Source of truth: active/non-deleted Tahseel client and exact `tbl_clients.sas_username`.
- Identity matching: exact normalized SAS username only; names/phones/fuzzy matching prohibited for status/control.
- Failure semantics: timeout/auth/malformed provider response is unavailable, never offline/not-found.

## Security and Side Effects

- Admin routes retain existing admin authentication/authorization boundary; mobile retains JWT and active-client scope.
- Credentials, tokens, headers, and raw rich status payloads remain backend-only and redacted.
- Allowed side effects: isolated worktree file edits and test-local state only.
- Prohibited without further approval: real SAS create/enable/disable/disconnect/profile changes, production edits, `.env` edits, commit/push/PR/merge/deploy.
- Writes: closed action set, maximum one provider write, no automatic retry, post-write verification when provider supports a deterministic field.
- Errors: normalized safe codes; no secret or arbitrary-account disclosure.

## Acceptance Scenarios

| Scenario | Setup | Action | Expected |
|---|---|---|---|
| Status happy path | scoped linked clients | request batch | one provider batch/cache lookup and exact statuses |
| Unauthorized/out of scope | unknown or inactive ID | request batch | omitted/no existence disclosure |
| Invalid input | duplicate/string/fractional/oversized IDs | request | safe 422, no provider call |
| Dependency failure | timeout/auth/malformed SAS response | status request | linked clients are unavailable |
| Write ambiguity | control provider response unclear | control | no retry; safe failure/verification failure |
| Missing config | credential absent | any gateway call | not_configured without network |

## Required Tests

- RED: focused new gateway/controller behavior test before each production slice.
- Focused GREEN: gateway, status endpoint/service, timeout, and controller suites.
- Regression: complete PHPUnit suite with baseline distinction.
- Static: PHP syntax, routes, diff/whitespace, direct-transport usage scan.
- Security/performance: secret scan, provider write-count assertions, one-vs-maximum batch call count.

## Release/Deployment Impact

- Build artifact: none.
- Migration: none.
- Configuration: tracked defaults become fail-closed; production private values already exist and remain untouched.
- Rollback: revert the eventual feature commit; no data rollback required.
- Production verification: read-only authenticated search and status shape/timing as `www-data`; no controls.

## Completion Evidence

- [ ] RED observed for expected reason
- [ ] Focused and full GREEN
- [ ] Independent PASS/BLOCK audit
- [ ] CI success after later commit/push approval
- [ ] Production verified if later deployed
