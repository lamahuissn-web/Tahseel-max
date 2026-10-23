# Spec 021 — Loyalty Bonus (Free Month)

## Problem
Customers have no incentive to pay on time beyond avoiding disconnection. The owner
wants a loyalty reward: a customer who consistently pays on time earns a free month.
The reward must be fully controllable by an admin (grace period, manual grants,
approval, redemption) because it is a future-facing program that will be tuned over time.

## Rule (locked)
- A customer earns **1 free month** after **24 on-time paid subscription months**.
- Scope: **subscription invoices only**, **internet customers only**, and **active
  customers only**. Service invoices (`invoice_type = 'service'`) never count;
  **satellite customers** (`client_type = 'satellite'`) and **inactive customers**
  (`is_active = 0`) are not part of the program (owner decisions, 2026-10-21).
  Live data: 228 satellite + 426 inactive are excluded, leaving 936 customers in the
  program. `bonus_client_types` keeps the type list editable; reactivating a customer
  brings his count straight back because nothing is stored.
- Counting starts **2026-01-01** (`bonus_count_start_date`). Nothing before that date counts.
- The earned free month is delivered as an invoice with **amount 0**.
- The free month **does not count** as a paid month toward the next 24.
- **Admin approves** the earned bonus. After approval the system applies the 0-invoice
  automatically to the customer's next monthly cycle (no hand-typed invoice).
  The free month is **the very next cycle month**, carrying that cycle's **normal
  `due_date`** (same day-of-month as the subscription). Example: 24th point earned in
  month M ⇒ month M+1 is free, invoiced 0 with M+1's usual due date.
- After redemption the streak **resets to 0** and counting restarts from the redeemed month.
- Only **Super-Admin (role 1)** and **accounting (role 7)** may access the section —
  i.e. the admin staff and management. The `collector` role (6) is excluded.
- No banking: a customer holds at most one un-redeemed bonus at a time.

## Definitions
**On-time month** — a fully paid subscription invoice whose payment landed **before the
next month's invoice became due** (i.e. paid within its own month). Month identity is
the invoice's `due_date` month (`Y-m`), matching how receipts already derive the paid month.
An editable `bonus_grace_days` adds extra tolerance on top; `0` means the month rule alone.

**Non-compliant month** — a new month's invoice has become due while the previous month
is still unpaid.

**Break** — the number of consecutive unpaid subscription months at the moment a new
invoice becomes due.

**Catch-up (استدراك)** — a break of 1–2 months that the customer clears by paying the
arrears together with the current month, before the following month's invoice is due.
Both months are then credited. Limited to `bonus_catchup_per_cycle` occurrences per
cycle (default 1, editable).

**Carried arrears (الدوامة)** — the customer pays each new month while leaving an older
invoice unpaid. A month is credited only if **no older subscription invoice was still
unpaid** at that month's due date. Paying the newest bill while an older one stays open
earns nothing.

The «دوامة» list is built from three conditions: 2+ open subscription invoices, the oldest
open one at least 35 days old, and the last payment dated **after** the oldest open bill
(so a customer who simply stopped paying is a defaulter, not a spin case). Live data: 97
internet customers.

**Spin state (دوامة)** — carried arrears persisting for >= `bonus_spiral_after_months`
consecutive month boundaries. Progress freezes at `0` until the arrears are cleared.

**Hard reset** — a break longer than `bonus_hard_reset_after_months` (default 2, i.e.
3+ consecutive unpaid months) makes the customer non-compliant and the streak drops to
`0`. Catch-up does not apply.

**Customer status** (shown in the admin list) — the status describes the position
**today** and must be **earned back**, never inherited from history:

| status | meaning |
|---|---|
| 💎 ممتاز | no arrears **and** never paid after an invoice's own due date (+ grace) in this cycle, with at least `bonus_recovery_months` clean months |
| 🟢 منتظم | no arrears and `bonus_recovery_months` consecutive clean months (default 3) since the last break |
| 🟡 عائد للانتظام | no arrears, but fewer than `bonus_recovery_months` clean months since the last break/catch-up |
| 🟠 متأخر | one month behind, still inside the catch-up window |
| 🔴 دوامة | pays the newest bill while an older one stays unpaid — earns nothing |
| ⚫ متعثر | break longer than 2 months — streak is 0 |

A used catch-up therefore stops labelling the customer as soon as he has strung
together `bonus_recovery_months` clean months; the fact stays visible in his panel.
A clean month = a month paid inside its own window (before the next bill).

**Payment-habit axis** (small badge, independent from the status) — measured on the
median delay between each invoice's own due date and its payment:

| band | median delay | meaning |
|---|---|---|
| ⚡ دقيق | ≤ 0 days | pays on or before the due date |
| ✅ خلال أسبوع | 1–7 days | pays within the first week |
| 🕐 متأخر عادةً | 8–15 days | habitually late |
| 🐢 مماطل | 16–30 days | pays late every month |
| ⏳ مماطل جداً | 31+ days | rarely pays before the next bill |

Five bands is the most the data supports: splitting each customer's history in
halves keeps 91% of them within one band and the split-half correlation is r = 0.64.
The band is deliberately separate from the status, so a customer who pays 12 days
late but owes nothing reads «🟢 منتظم · 🕐 متأخر عادةً» instead of losing a tier.

**Commitment score** (0–100) = `45%` position today + `35%` share of months actually
credited + `20%` punctuality (`1 − median delay / 30`). No payment history ⇒ 0.
A two-day delay nudges the score, it never drops a whole tier.

**Streak** — consecutive on-time months after the later of:
- `bonus_count_start_date`, and
- the redemption date of the customer's last redeemed bonus.

**Progress** — number of months in the current streak, `0 … bonus_months_required`.

**Eligible** — progress `>= bonus_months_required` and no un-redeemed bonus exists.

## Data model

### New table: `tbl_client_bonuses`
| column | type | notes |
|---|---|---|
| id | bigint PK | |
| client_id | bigint, indexed | FK to `tbl_clients` |
| status | varchar(20) | `pending` / `approved` / `redeemed` / `cancelled` |
| source | varchar(20) | `auto` / `manual` |
| months_counted | int | months in the streak that earned it (normally 24) |
| cycle_start | date | first month of the counted streak |
| cycle_end | date | last month of the counted streak |
| earned_at | datetime | when eligibility was detected/created |
| approved_by | bigint, nullable | admin id |
| approved_at | datetime, nullable | |
| redeemed_at | datetime, nullable | |
| redeemed_invoice_id | bigint, nullable | the 0-invoice created |
| notes | text, nullable | |
| created_by / updated_by | bigint, nullable | |
| timestamps, deleted_at | | soft deletes for audit |

**No changes to any existing table.** No columns added to `tbl_invoices`,
`tbl_clients`, `tbl_revenues`, or `tbl_financial_transactions`.

### Settings (`app_config` keys)
| key | default | purpose |
|---|---|---|
| `bonus_enabled` | `0` | master switch |
| `bonus_months_required` | `24` | months needed per free month |
| `bonus_grace_days` | `0` | extra tolerance in days on top of the month rule; editable at any time |
| `bonus_catchup_per_cycle` | `1` | catch-up (استدراك) occurrences allowed per cycle; `0` disables |
| `bonus_recovery_months` | `3` | clean months needed to return to 🟢 منتظم after a break |
| `bonus_hard_reset_after_months` | `2` | a break longer than this resets the streak to 0 |
| `bonus_spiral_after_months` | `2` | consecutive month boundaries with carried arrears ⇒ spin state |
| `bonus_client_types` | `internet` | client types included; satellite excluded by default |
| `bonus_count_start_date` | `2026-01-01` | nothing before this date counts |
| `bonus_require_zero_balance` | `1` | block redemption while the customer owes |
| `bonus_invoice_notes` | `شهر مجاني - بونص` | text stored on the 0-invoice |

## Calculation
Progress is **computed, never stored**, so it self-heals after restores or payment edits.
No stored counter can drift out of sync with the payments.

Per customer:
1. Determine `since` = later of `bonus_count_start_date` and the last `redeemed_at`.
2. Fetch that customer's subscription invoices with `due_date >= since`, plus their
   payment revenue (`paid_date`) and excluding invoices that are themselves
   bonus free months (id present in `tbl_client_bonuses.redeemed_invoice_id`).
3. Sort by `due_date`; mark each month on-time or late.
4. Walk the sorted months and count the longest **trailing** consecutive on-time run
   (a late or missing month breaks the run; counting resumes with the next on-time month).

Per list page: **one query per page** fetching all subscription invoices + revenues for
the visible clients, grouped by `client_id`. Never a query per client row (no N+1).

## Admin section: «البونص والمكافآت»
Standalone menu section, admins only.

### 1. الإعدادات
All keys above, editable. Toggling `bonus_enabled` off hides the program from
calculation and from the mobile API without deleting data.

### 2. قائمة العملاء
Columns: customer, subscription, **progress `x / 24`**, **status**, last counted month,
next eligible month.
Statuses: `قيد التقدم` / `مستحق` / `معتمد` / `مستبدل` / `متوقف` / `موقوف (عليه دين)`.
Health column (computed): 💎 ممتاز · 🟢 منتظم · 🟡 عائد للانتظام · 🟠 متأخر · 🔴 دوامة · ⚫ متعثر
Filters: مستحق الآن · قريب من الاستحقاق · لديه بونص معتمد · عليه دين · **دوامة** · الكل.
Export to Excel (existing export pattern).

### 2b. تبويب «الدوامة»
A dedicated tab listing customers whose newest months are paid while older ones stay
open (real count on the current data: 167 customers). Columns: customer, unpaid count,
oldest unpaid month, amount owed, last payment date. This is the "how do I spot him"
view — the customer is identifiable by **older unpaid invoices coexisting with recent
payments**, sorted by how long the oldest has been open.

### 3. صفحة العميل
- 24-month grid: ✓ on-time · ✗ late · — unpaid
- Current progress and next eligible date
- Actions: `منح يدوي` · `اعتماد` · `تأجيل` · `إصدار الفاتورة` · `إلغاء` · `حذف المعلّق` · `إعادة تعيين الدورة`
- Every action writes an audit log entry and sends a Telegram alert
  (new event `bonus_granted` / `bonus_redeemed`).

### 4. سجل البونص
Full history: who created/approved/postponed/redeemed, when, the counted cycle, the invoice id.
Bonus row statuses: `pending` (بانتظار القرار) · `approved` (معتمد، لم يُصدر) ·
`postponed` (مؤجّل) · `redeemed` (مُصدر) · `cancelled` (ملغى).

## Redemption flow (administration decides — nothing is automatic)
1. Daily command `bonus:sync` (scheduled 02:20, chunked by 200, `--dry-run` available)
   scans active in-scope clients, computes progress, and creates a `pending` row when the
   threshold is reached. Idempotent: **one open bonus per customer** at a time, whatever
   its open status; it respects `bonus_enabled` and writes nothing financial. A single
   Telegram summary lists the customers it recorded.
2. Administration decides, per customer:
   - **اعتماد** → `approved`: the right is acknowledged, still **no invoice**.
   - **تأجيل** → `postponed`: the right is kept, the decision waits for the company's
     situation. Requires a reason; an optional target month (`YYYY-MM`) is recorded,
     plus `postponed_at` / `postponed_by`.
   - **إصدار الفاتورة** → only an explicit admin click creates the 0-invoice
     (`redeemed`, `redeemed_invoice_id`, `redeemed_at`), due on the next cycle's
     normal day-of-month. Allowed for `approved` **and** `postponed` bonuses.
   - **إلغاء** → `cancelled` (no invoice ever existed).
   - **حذف** → hard delete, `pending` only (a mistaken click).
3. The system never issues on its own: the auto-apply switch was removed
   (`bonus_auto_apply_on_approve` deleted from `app_config`), and monthly invoice
   generation (`app:add-new-invoices`) is untouched — a free month is only created by
   a human decision.
4. `bonus_require_zero_balance` still applies at issue time: with an unpaid balance the
   issue button is disabled with the reason shown, and the bonus stays open.
5. A postponed bonus never expires: the right is carried until administration issues or
   cancels it. Postponed rows are counted on the dashboard (⏸ مؤجّل) so they are not
   forgotten.

## Edge cases
| case | behaviour |
|---|---|
| Customer pays late but within his month | counted as on-time — this is the normal case in real data (e.g. paid 9 days after `due_date` but before the next bill) |
| Customer breaks 1–2 months, then pays both together | catch-up: both months counted, one allowance consumed (`bonus_catchup_per_cycle`) |
| Catch-up allowance already used, breaks again | that month is not counted; earned progress is kept |
| Customer breaks more than 2 months | hard reset: streak = 0, catch-up does not apply |
| Customer pays the newest month but leaves an older one unpaid (دوامة) | the new month is **not** credited; status 🔴 دوامة; nothing accrues until arrears are cleared |
| Customer owes 08+09, pays only 08, now owes 09+10 | still 🔴 دوامة — paying the oldest is correct ordering but the balance never reaches zero, so every month boundary carries arrears |
| Spin customer finally clears to zero | exits spin; counting restarts at his next on-time month |
| Customer pays 2 months in advance | both months count as on-time; only one month per calendar month counts |
| Customer is a satellite subscriber | not part of the program at all — never counted, never listed, never in the spin tab |
| Customer is inactive (stopped) | same: status `inactive`, no counting, no grant; his history returns untouched if he is reactivated |
| Customer stops paying entirely | not a spin case (no payment after the oldest open bill) — he is a defaulter |
| Customer owes money at cycle time | bonus waits as `approved`; no 0-invoice until clear |
| 0-invoice itself | excluded from streak counting; never appears as debt; triggers no payment receipt (amount 0) |
| Bonus applied then voided/deleted | bonus row returns to `approved`, `redeemed_invoice_id` cleared by admin action |
| Master switch off | sync stops, no new bonuses, existing rows untouched |
| Customer deactivated/suspended | excluded from sync until reactivated |
| Duplicate sync runs | unique open-bonus guard prevents a second row |
| Manual grant | creates a `pending`/`approved` row with `source = manual`; skips the 24-month rule |

## Non-goals
- No change to payments, revenues, transfers, or balances.
- No schema change to any existing table.
- No automatic discounting of existing unpaid invoices.
- Mobile display is a later phase.

## Phases
- **P1** — schema, settings, admin list + customer detail (read-only progress)
- **P2** — manual grant, approve/cancel, create 0-invoice, reset
- **P3** — `bonus:sync` command + Telegram alerts + audit log entries
- **P4** — mobile API: `bonusProgress`, `bonusStatus`, `nextEligibleDate`

## Open items
**None — all design questions answered (2026-10-21).**

Resolved in review (2026-10-21):
- Access: Super-Admin (role 1) + accounting (role 7). Collector (role 6) has no access.
- On-time = paid within its own month (before the next bill is due), editable grace on top.
- A late month is not counted; earned progress is kept. Manual reset button covers punishment.
- Free month = the next cycle month with its normal `due_date`; admin approves.
- Catch-up once per cycle, editable; break > 2 months ⇒ streak 0.
- Spin customers (older unpaid + recent payments) earn nothing until arrears reach zero.
- Verified against real data: عامر يعقوب (#1576) breaks after his 1st month; جمال يعقوب (#518)
  keeps 9/9 months; 167 customers currently sit in the spin state.

## Safety
- Read-only aggregation for progress; writes limited to the new bonus table and the
  single generated 0-invoice.
- All redemption writes inside a transaction with a guard so a bonus cannot be
  redeemed twice.
- Financial invariants unchanged: no revenue is created, no balance is touched.

## Evidence
- Counting start `2026-01-01` with 24 required months means the earliest possible
  redemption is ~January 2028 — no payouts in 2026 or 2027.


## Mobile section (Phase 4 — read-only)

`GET /api/v1/bonus/summary` · `GET /api/v1/bonus/clients` · `GET /api/v1/bonus/client/{id}`

- **Access:** roles 1 (Super-Admin) and 7 (accounting). Any other role — including the
  collector — gets `403 bonus_forbidden`. The menu item is hidden in the app, but the
  server is the enforcement point.
- **Read-only by design:** no grant, approval, postponement or issuance from the phone.
  Money moves only from the web screen.
- **Payload:** progress/required, status + Arabic label, habit badge + label, score,
  next eligible month, and the open-bonus state; the detail endpoint adds the month grid,
  the habit sentence and the bonus history.
- **Cost:** one cached page-level progress map (10 min) — warm responses 0.06–0.07s for
  947 customers; no query per row.
- **Master switch off:** every endpoint answers `enabled:false` with empty rows so an old
  app version degrades quietly.
