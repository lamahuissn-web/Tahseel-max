# Tasks — 021 Loyalty Bonus

**Branch:** `feature/021-loyalty-bonus` (started 2026-10-21)
**Status:** Phase 0 + Phase 1 (progress, read-only) + settings screen are implemented and green.
Phase 2 actions (grant/approve/apply) and Phases 3–4 are still open.

## Phase 0 — Foundation
- [X] Migration `create_tbl_client_bonuses` (+ index `(client_id, status)`).
- [X] `App\Models\ClientBonus` with status constants + `open()` scope.
- [X] `App\Services\Bonus\BonusSettings` cached reader over `app_config`.
- [X] Seed default `bonus_*` keys in `app_config` (master switch off by default).

## Phase 1 — Progress (read-only)
- [X] RED test: paid 9 days after `due_date` but before the next bill counts as on-time
      (real case: جمال يعقوب #518 → 9/9 months credited on live data).
- [X] RED test: a month still unpaid when the next bill arrives is not counted
      (real case: عامر يعقوب #1576 → 1/24, status late).
- [X] RED test: `bonus_grace_days` extends tolerance and is read live from settings.
- [X] RED test: catch-up — 1–2 broken months paid together before the next bill credit both.
- [X] RED test: catch-up is limited to `bonus_catchup_per_cycle` per cycle.
- [X] RED test: break of 3+ unpaid months resets the streak (hard reset).
- [X] RED test: oldest-first rule — paying the newest month while an older one is open
      credits nothing (real case: رواد موسي #702 → 0/24).
- [X] RED test: carried arrears on 2 boundaries ⇒ status spiral, progress 0.
- [X] RED test: clearing the arrears restores crediting from the next month.
- [X] RED test: service invoices never count.
- [X] RED test: 0-invoice (redeemed bonus) is excluded from counting.
- [X] RED test: streak restarts from `redeemed_at` after redemption.
- [X] RED test: nothing before `bonus_count_start_date` counts.
- [X] RED test: progress capped at the required months.
- [X] RED test: open bonus blocks eligibility (no banking).
- [X] RED test: query count does not grow with the number of clients (no N+1).
- [X] RED test: satellite customers are excluded (0 progress, status `excluded`).
- [X] RED test: satellite customers never appear in the spiral list.
- [X] RED test: opting satellite back in from settings brings them back.
- [X] Satellite exclusion + `bonus_client_types` setting (internet only by default).
- [X] Inactive (`is_active = 0`) customers excluded: status `inactive`, no grant, not listed.
- [X] A bill that is not due yet never breaks the run (241 future invoices on live data).
- [X] Spin list refined: 2+ open, oldest ≥ 35 days, last payment after the oldest open bill.
- [X] Implement `BonusProgressService::forClients()` / `::forClient()` / `::spiralClients()`.
- [X] `BonusController@index` — customer list, filters, summary cards, pagination.
- [X] `BonusController@show` — customer panel with the month grid.
- [X] `BonusController@spiral` — the «دوامة» tab (167 customers on live data).
- [X] Blade views under `resources/views/dashbord/bonus/`.
- [X] Menu entry «البونص والمكافآت» gated to roles 1 + 7.
- [X] RED test: role guard — collector (6) 403 on every route; roles 1 and 7 get 200.
- [X] RED test: settings screen persists the edited values.

## Phase 2 — Admin control
- [X] RED test: manual grant creates one open bonus, `source = manual`.
- [X] RED test: a second open bonus for the same client is rejected.
- [X] RED test: approve sets `approved_by` / `approved_at` and auto-issues when no debt.
- [X] RED test: cancel removes the open bonus without touching payments.
- [X] RED test: apply-now blocked while the customer has an outstanding balance.
- [X] RED test: apply-now creates exactly one invoice, amount 0, settled, no revenue row,
      `due_date` = next cycle month with the normal day-of-month.
- [X] RED test: apply-now cannot run twice on the same bonus.
- [X] RED test: reset clears the open bonus, moves the origin and wipes earlier points.
- [X] RED test: manual points raise progress (also when the cycle has no invoices yet).
- [X] RED test: satellite customers cannot be granted a bonus.
- [X] RED test: collector gets 403 on the action routes; admin can grant + approve.
- [X] Implement `grant` / `approve` / `cancel` / `apply` / `points` / `reset` routes.
- [X] Implement bonus log screen `bonus/log` (bonuses + adjustments).
- [X] Audit-log entry + Telegram alert on every action.
- [X] `tbl_client_bonus_adjustments` ledger for manual points and resets.

## Phase 3 — Automation
- [ ] RED test: `bonus:sync` creates a pending row exactly once (idempotent re-run).
- [ ] RED test: `bonus:sync` skips inactive clients.
- [ ] RED test: `bonus:sync` respects the master switch.
- [ ] Implement `bonus:sync` command (chunked, page-level maps).
- [ ] Register the command in the scheduler (daily, off-peak).
- [ ] Auto-apply hook on monthly invoice generation for approved bonuses.
- [ ] Telegram events `bonus_earned` / `bonus_redeemed` + toggles in the settings form.

## Phase 4 — Mobile
- [ ] RED test: `GET /api/v1/clients` exposes `bonusProgress`, `bonusStatus`, `nextEligibleDate`.
- [ ] RED test: those fields are null when the master switch is off.
- [ ] RED test: query count unchanged versus the current endpoint.
- [ ] Implement the fields in `ClientResource` fed by the page-level map.

## Verification & delivery
- [X] Manual comparison of computed progress vs real customers (عامر / جمال / رواد).
- [ ] Confirm invoices / revenues / balances are identical before vs after the feature.
- [ ] Full neighbouring test suites green.
- [ ] Independent read-only audit PASS.
- [ ] Commit + push on `feature/021-loyalty-bonus` (no merge to main).
- [ ] Owner acceptance test on the development server.

## Deferred — resolved with the owner (2026-10-21)
- [X] Grace period: paid inside its own month; extra days editable at any time.
- [X] Late month: not counted, earned progress kept; break > 2 months zeroes the run.
- [X] Access: Super-Admin (1) + accounting (7); collector (6) refused.
- [X] Free month: next cycle month with that cycle's normal `due_date`, admin approves.
