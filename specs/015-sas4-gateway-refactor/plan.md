# Implementation Plan

## Architecture

1. Keep `Sas4ApiService` as a low-level transport adapter only.
2. Add `Sas4Gateway` as the application-facing façade. It receives trusted clients/client IDs and exposes normalized use-case methods.
3. Reuse `ClientSasStatusService` inside the gateway for batch status resolution rather than duplicating status logic.
4. Migrate API/admin controllers and `Sas4AutoMatch` to depend on the gateway.
5. Keep route names stable; update the admin Blade callers from usernames to client IDs.

## Normalized contracts

- Status row: `client_id`, `sas_username`, closed status enum.
- Read result: success flag/code plus approved payload for existing UI compatibility.
- Control result: success, code, action, client_id, sas_username, and verification state; no token/raw transport data.
- Configuration failure: deterministic `not_configured` without an HTTP request.

## Vertical TDD slices

1. Gateway configuration guard and status delegation.
2. Gateway exact client-link resolution and normalized read failures.
3. Strict client-ID admin batch status contract plus Blade migration.
4. Search/profile façade and controller migration.
5. Client information/traffic façade and controller migration.
6. Control façade: closed actions, one write maximum, post-write verification where possible.
7. Link/create orchestration migration without changing save semantics.
8. Auto-match command migration while preserving dry-run behavior.

## Expected files

- `app/Services/Sas4/Sas4Gateway.php` — new application integration boundary.
- `app/Services/Sas4/Sas4ApiService.php` — transport configuration guard/support only.
- `app/Services/Sas4/ClientSasStatusService.php` — retained hardened status resolver.
- `app/Http/Controllers/Admin/ClientController.php` — thin gateway calls.
- `app/Http/Controllers/Api/ClientSasStatusController.php` — gateway status entry.
- `app/Console/Commands/Sas4AutoMatch.php` — gateway search.
- Active client Blade views — client-ID status payload.
- `config/sas4.php` and `.env.example` — fail-closed config/documentation.
- Focused feature/unit tests and this spec directory.

## Safety

- Isolated worktree and `feature/sas4-gateway-refactor` branch from clean `production`.
- No database migrations or writes during verification.
- No live enable/disable/disconnect/profile/create actions.
- Live verification limited to configuration presence, authentication/read-only search, timing, and response shape.
- Tests use array cache and test-only configuration; never source production secrets.
- Preserve production checkout untouched.

## Verification

1. Capture RED and GREEN per vertical slice.
2. Run all SAS4 gateway/status/API/controller tests.
3. Run the full relevant PHPUnit suite and record pre-existing failures separately.
4. Run PHP syntax checks for every modified PHP/Blade file.
5. Verify route registration and rendered Blade request payloads.
6. Scan diff for credentials, token leakage, N+1 status loops, and direct low-level client usage.
7. Run a read-only live SAS search as `www-data`, reporting only success, count, and timing.
8. Independent fresh-context security/logic/performance audit.

## Rollback

Revert the feature commit. No schema or data migration is involved; existing `sas_username` links and production `.env` remain compatible.
