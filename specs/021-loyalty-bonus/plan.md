# Plan — 021 Loyalty Bonus

Spec: `specs/021-loyalty-bonus/spec.md`

## Principles
- Test-first (RED before GREEN), same as spec 020.
- Zero changes to existing financial tables. New table + `app_config` keys only.
- Progress is computed on read; the only stored state is the bonus ledger row.
- Every write path runs inside a transaction with a guard so a bonus cannot be
  created twice or redeemed twice.
- Admin-only routes, gated by the existing role middleware used elsewhere in `routes/admin.php`.

## Phase 0 — Foundation
1. Migration `create_tbl_client_bonuses` with the columns in the spec, plus:
   - index on `(client_id, status)`
   - unique index `(client_id, status)` for `pending`/`approved` is not expressible
     in MySQL, so enforce "one open bonus per client" in the service layer with a
     locked existence check inside the transaction.
2. `App\Models\Admin\ClientBonus` with status constants and relations
   (`client`, `invoice`, `approvedBy`).
3. `App\Services\Bonus\BonusSettings` — cached reader over `app_config`
   (`bonus_*` keys), mirroring `TelegramConfig`.
4. Seed default `app_config` keys (all off/neutral until the owner enables them).

## Phase 1 — Progress calculation (read-only)
5. `App\Services\Bonus\BonusProgressService`:
   - `mapForClients(array $clientIds): array` — one query per page:
     subscription invoices in range + their revenue `paid_date`, excluding invoices
     present in `tbl_client_bonuses.redeemed_invoice_id`.
   - `forClient(int $clientId): BonusProgress` — single client detail.
   - `since` = later of `bonus_count_start_date` and the last `redeemed_at`.
   - Month key = `Y-m` of `due_date`; on-time = invoice fully paid **before the next
     invoice's `due_date`** (paid within its own month), plus optional `bonus_grace_days`.
   - A month whose next bill arrived while it was unpaid is non-compliant: not counted,
     earned progress kept.
   - Break > `bonus_hard_reset_after_months` consecutive unpaid months ⇒ streak = 0.
   - Break of 1–2 months cleared before the following bill ⇒ catch-up: both months count,
     up to `bonus_catchup_per_cycle` per cycle.
   - **Oldest-first rule:** a month is credited only if no older subscription invoice was
     unpaid at that month's due date (kills the دوامة loophole).
   - `carriedArrearsMonths` = consecutive month boundaries with an older unpaid invoice;
     `>= bonus_spiral_after_months` ⇒ spin state, progress reported as 0.
   - `status` enum for the admin list: compliant / catchup_used / late / spiral / hard_reset.
   - Longest trailing consecutive on-time run.
6. Admin controller `BonusController@index` (customer list) with filters:
   eligible, near eligible, has open bonus, has debt, spiral, all. Server-side pagination.
   Routes sit behind the existing admin auth + role middleware restricted to roles **1** and **7**.
7. `BonusController@show` — customer panel with the 24-month grid
   (`on_time` / `late` / `unpaid`).
8. Blade views under `resources/views/dashbord/bonus/` matching the existing
   Keen/Metronic markup used by other admin screens.

## Phase 2 — Admin control
9. `POST bonus/{client}/grant` — manual bonus (`source = manual`), pending or
   approved depending on the submitted flag.
10. `POST bonus/{id}/approve`, `POST bonus/{id}/cancel`.
11. `POST bonus/{id}/apply-now` — creates the 0-invoice immediately:
    - guard: status `approved`, no outstanding balance when
      `bonus_require_zero_balance` is on
    - invoice: `invoice_type = subscription`, `amount = 0`, remaining 0,
      settled, notes from `bonus_invoice_notes`
    - `due_date` = the next cycle month carrying the subscription's normal day-of-month
      (same rule the monthly generator uses)
    - sets `redeemed_at`, `redeemed_invoice_id`, status `redeemed`
12. `POST bonus/{client}/reset` — admin override: clears the open bonus and
    moves the streak origin forward.
13. Settings screen `bonus/settings` writing the `bonus_*` keys.
14. Bonus log screen `bonus/log`.

## Phase 3 — Automation
15. Artisan command `bonus:sync` — scans active clients, creates `pending` rows for
    newly eligible customers, idempotent, chunked (no N+1: page-level maps).
16. Scheduler entry in `app/Console/Kernel.php` (daily, off-peak).
17. Auto-apply hook: when a monthly invoice is generated for a client with an
    `approved` bonus and a clear account, generate that invoice at 0 and mark the
    bonus redeemed.
18. Telegram events `bonus_earned` / `bonus_redeemed` + toggles in the settings form
    (same pattern as the events added today).

## Phase 4 — Mobile
19. Extend `ClientResource` with `bonusProgress`, `bonusStatus`, `nextEligibleDate`
    (null-safe, no per-row queries — fed by the same page-level map).
20. Feature test pinning the query count does not grow with page size.

## Testing strategy
- RED first for each phase: `tests/Feature/BonusProgressTest`,
  `BonusGrantTest`, `BonusRedemptionTest`, `BonusSyncCommandTest`,
  `BonusApiFieldsTest`.
- Progress tests must cover: paid-within-month boundary, catch-up, hard reset, oldest-first
  (spin) rule, prepaid month, the 0-invoice being excluded, reset after redemption, and the
  `2026-01-01` floor.
- Redemption tests must prove: no double redemption, blocked while in debt,
  exactly one invoice created, no revenue row created, balances unchanged.
- A query-count assertion for the list and API paths (no N+1), same style as
  spec 020's test.

## Verification against real data
- Run on the development database, compare computed progress for a sample of
  clients against a manual query, and confirm no financial totals move
  (invoices/revenues/balances before vs after).

## Delivery
- Independent read-only audit, then commit + push on the active development branch.
- Nothing merged to main, nothing deployed to production without explicit approval.
