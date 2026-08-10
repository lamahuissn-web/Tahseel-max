# Implementation Plan

## Architecture

- Add `status_timeout_seconds` to `config/sas4.php`, sourced from `SAS4_STATUS_TIMEOUT_SECONDS` with default `4`.
- Keep `ClientSasStatusService::EXTERNAL_TIMEOUT_SECONDS = 4` as the canonical compatibility default.
- When no explicit constructor timeout is supplied, resolve the deployment value from Laravel config.
- Accept canonical positive whole-second values in the inclusive range `1..20`; invalid values fall back to `4` so Backend configuration cannot exceed the Mobile receive-timeout ceiling.
- Preserve explicit constructor injection so existing timeout propagation tests remain deterministic and do not depend on global config.

## Files

- `config/sas4.php` — non-secret timeout config mapping.
- `.env.example` — deployment documentation with safe default.
- `app/Services/Sas4/ClientSasStatusService.php` — validated config resolution only.
- `tests/Feature/ClientSasStatusServiceTest.php` — RED/GREEN default, override, invalid, and boundary coverage.
- `specs/014-configurable-sas-status-timeout/*` — contract and evidence.

## Safety

- No production `.env` is read or modified during development/tests.
- No live SAS request, token creation, cache write outside the test process, customer query, payment, or WhatsApp action.
- Default behavior is unchanged.
- Invalid configuration is bounded and cannot create a zero, negative, fractional, or unreasonably long request.

## Verification

1. Focused config test RED before implementation.
2. Focused service tests GREEN.
3. SAS API timeout and endpoint regression tests.
4. PHP syntax/style checks and full relevant test suite.
5. Secret/diff scan.
6. Independent read-only PASS/BLOCK audit.
7. Commit/push/PR to `production`; merge only after green CI.
8. Deploy KIRA with default unchanged; provide friend instructions to set `20`, restore tracked source, and update cleanly.

## Rollback

Revert the merged commit. Deployments that added `SAS4_STATUS_TIMEOUT_SECONDS` may leave it in `.env`; older code safely ignores it.
