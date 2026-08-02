# Secure Mobile Invoice Collection — Backend Companion Spec

**Canonical product specification:** Tahseel-Mobile `specs/003-secure-mobile-payments/` on branch `feature/secure-mobile-payments`.

## Current Route Must Remain Unused by Mobile

`POST /api/v1/invoice/{id}/pay` is JWT-protected but currently lacks explicit `pay_invoice` authorization, request validation, row locking, durable idempotency, duplicate constraints, safe HTTP failure semantics, and post-commit side-effect isolation.

## Required Backend Boundary

Proposed endpoint: `POST /api/v1/invoices/{invoice}/payments`

Required invariants:
- authenticated active admin with `pay_invoice` and valid account;
- server-derived collector, account, client, status, timestamps, and balances;
- full locked remaining balance only; client sends `expected_remaining` and cannot choose an amount;
- required UUID `Idempotency-Key`, durably unique and request-hash bound;
- one transaction for operation, revenue, financial transaction, invoice state, and audit record;
- `lockForUpdate()` before eligibility and amount checks;
- stable collection reference and replayable response;
- one idempotent operation-specific WhatsApp receipt automatically queued after commit;
- real 401/403/404/409/422/500 status codes without internal exception details.

## Controlled Testing Authorization

KIRA confirmed this environment is isolated and authorized controlled financial/payment tests. Tests must use dedicated fixtures, verify all affected records and balances, avoid bulk operations, and rollback/clean up where possible.

## Blocking Product Clarifications

1. **Resolved:** mobile collectors pay the full remaining balance only; partial payments remain admin-only.
2. **Resolved:** automatically queue one idempotent WhatsApp receipt after commit.
3. **Resolved:** no collector note in v1.

All product clarifications are resolved. No implementation starts until KIRA approves the implementation checkpoint.
