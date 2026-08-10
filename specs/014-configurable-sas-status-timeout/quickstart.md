# Future Friend Backend Preflight

Use this checklist before building or distributing an APK for any independent Tahseel deployment. It is read-only until the operator explicitly approves a documented fix.

## 1. Identity and release source

- Confirm the Backend is on the approved `production` lineage and record the exact commit/tag.
- Back up the database and private `.env`; record a rollback path.
- Confirm the public API uses valid HTTPS and the intended deployment domain.
- Do not copy another deployment's database, JWT secret, SAS credentials, signing identity, or payment configuration.

## 2. Authentication readiness

- Confirm `JWT_SECRET` is present and loaded by Laravel without printing its value.
- Prove token creation with a non-customer test account or approved real account; report only success and token length.
- Wrong credentials must return the controlled invalid-credentials response, never a generic exception.
- After an approved `.env` change, clear/rebuild configuration as the web-service user and verify the loaded configuration.

## 3. Laravel runtime permissions

- Verify `storage`, `storage/framework/cache`, logs, and `bootstrap/cache` are writable by the web-service user.
- Do not run SAS/cache diagnostics as `root`; root-owned hashed cache paths can make a healthy SAS service appear unavailable.
- Never repair ownership until the actual permission failure is demonstrated and approved.

## 4. SAS readiness and timeout selection

- Verify SAS configuration is present without exposing credentials or encryption keys.
- Run token and all-users status checks as the web-service user with no customer payload in logs.
- Measure the cold all-users request and confirm the exact `sas_username` pilot appears once with authoritative `online_status` `0` or `1`.
- Choose `SAS4_STATUS_TIMEOUT_SECONDS` from the measured cold latency plus a small margin:
  - Default/fast deployment: omit it or use `4`.
  - Measured slower deployment: use a justified value up to `20`.
- Values outside `1..20` fall back to `4`.
- After changing the timeout, rebuild/clear the Laravel configuration cache (`php artisan config:clear`; if the deployment uses a config cache, rebuild it as the web-service user) and confirm the loaded value without printing secrets.
- If the cold request cannot reliably finish within `20` seconds, **BLOCK deployment** and optimize the SAS endpoint/query. Do not increase the timeout beyond the Mobile ceiling.
- Confirm a cold Tahseel SAS-status request completes below the Mobile timeout and a warm request uses the success-only cache.
- Preserve exact `sas_username` matching. Service failure is `unavailable`; it must never be labeled `offline`.

## 5. Payment safety

- Payment remains disabled unless the deployment independently proves migrations, `pay_invoice` authorization, ownership boundaries, full-balance-only behavior, locking, UUID idempotency, stale-balance rejection, and reconciliation.
- An unauthenticated `401` proves route protection only; it does not prove payment safety.
- Never execute a real payment during preflight without explicit approval.

## 6. Clean update procedure

- Deployment-specific values belong in private `.env`, not tracked source.
- Clear/rebuild Laravel config after approved environment changes.
- Confirm the deployed checkout is clean before updating so fixes cannot be silently overwritten.
- Run focused authentication/SAS tests and inspect logs after updating.
- Build the friend APK only after Backend preflight returns explicit `PASS`.
