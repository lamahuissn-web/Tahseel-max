# Feature 019 — WhatsApp Phone Validation (fail-fast, no provider calls for bad numbers)

## Goal
When a client has no phone number or an unsendable/wrong number (e.g. `961000000`),
the WhatsApp pipeline MUST NOT process/dispatch the message to the Zernio/Meta
provider. Today only *empty* phones are skipped; malformed numbers reach the
provider, burn API calls, fail opaquely, and confuse admins ("customer paid, no
receipt, why?").

## Requirements

### R1 — One central validator (single source of truth)
New `App\Services\WhatsApp\WhatsAppPhoneValidator` (static, no deps):
- Strips formatting noise (spaces, dashes, parens, dots), leading `+` / `00`.
- Reduces to Lebanese national significant number (strips `961` and leading `0`s).
- Accepts Lebanese MOBILE forms only: prefix `3` (7-digit) or `70|71|76|78|79|81` (8-digit).
- Rejects: empty, non-numeric residue, wrong length, unknown prefix, all-zero
  garbage (`961000000` -> reject), foreign formats.
- Returns `{valid, e164, reason}`; `e164` is `+961<NSN>` on success.
- Assumption documented: deployment is Lebanon-only (both known servers are).
  Prefix list lives in one constant for future extension.

### R2 — Entry-point gating (skip BEFORE enqueueing)
Replace/supplement every `empty($phone)` guard on subscriber paths:
- `PaymentReceiptNotifier`: invalid phone -> DO NOT enqueue. Still create the
  log row (existing `firstOrCreate` on `payment_reference`) but as
  `status=failed`, `error="Invalid phone number (<reason>): <raw>"`, so the
  admin sees WHY no receipt arrived. Store normalized `e164` when valid.
- `MonthlyReminderNotifier`: invalid phone -> skip exactly like empty phone
  (log + `not_applicable`), no row created.
- `ReminderService` enqueue paths: treat invalid like missing phone (existing
  failed-log pattern, updated wording).
- `WhatsAppControlCenterController`: manual send page rejects invalid phone
  with a validation error; bulk/calendar selection skips invalid numbers and
  reports them in the summary/log.

### R3 — Delivery-time backstop (defense-in-depth)
`SendWhatsAppMessage::deliver()` validates AGAIN before calling the provider
(same pattern as the 2026-08-21 kill-switch re-check). Invalid -> `markFailed`
with explicit reason, `$service->send()` never called. Catches legacy queued
rows and any future code path that forgets R2.

### R4 — Never block business operations
A bad phone must never fail a payment or break a bulk run. Payment saves,
invoice updates, and remaining clients in a batch continue normally. The ONLY
affected thing is the WhatsApp send (skipped or logged-failed).

### R5 — Normalized storage
Where valid, log rows store the normalized `+961…` form (matches existing DB
shape and Zernio's digits-only conversation matching).

## Non-goals
- Collector reminder command stays OUT of scope (staff free-text path, per Spec 018).
- NO data cleanup/migration of existing `tbl_clients.phone` values.
- NO admin UI listing invalid numbers (possible follow-up).
- NO production merge; work stays on `spike/zernio-whatsapp-adapter`.

## Acceptance criteria
1. Client with phone `961000000` pays -> invoice paid normally, receipt row
   exists with `status=failed` + invalid-phone error, ZERO provider calls,
   queue untouched.
2. Client with empty phone -> unchanged behavior (skip, no row).
3. Valid `+961 3 123456` / `70123456` / `96170123456` -> normalized and sent.
4. Queued legacy row with garbage phone picked up by worker -> marked failed,
   provider never called.
5. All existing WhatsApp tests still green.
