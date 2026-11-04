# Spec 023: Telegram Notification Message Format

## Problem

Every operator alert is assembled inline with `sprintf()` at its call site, in
seven different files. Eighteen call sites, fifteen event keys, eighteen
different phrasings. The result on the phone is a single long Arabic sentence:

```
تم دفع مبلغ 25.00 $ للعميل ابو محمود سلمون W.K، وكان تاريخ الاستحقاق 2026-10-20. (تمت العملية بواسطة: قاسم محمد عامر)
```

Four concrete defects follow from that shape:

1. **Unreadable at volume.** Production averages **36.8 payments/day** (94 on the
   busiest day in the last 30 days, 63 today). A wall of one-line paragraphs is
   not scannable; the owner cannot tell a payment from an edit from a delete
   without reading every word.
2. **The send can fail silently on valid data.** `TelegramApiClient::sendMessage()`
   posts with `parse_mode=HTML`, but no call site escapes the values it
   interpolates (client name, notes, admin name). A customer name or an expense
   description containing `&`, `<` or `>` makes Telegram answer `400 can't parse
   entities`, the helper returns `false`, and nothing is logged and nothing is
   retried. The operator simply never learns about that operation.
3. **No way to act on a message.** A payment alert names an invoice number but
   carries no link; the owner must search the panel by hand.
4. **Inconsistent anatomy.** Some messages carry an emoji, some do not; the actor
   is inside parentheses in one, appended with `-` in another, and missing
   entirely in the transfer and overdue-reminder messages.

## Requirement

### R1 — One builder, one anatomy

A single class produces every message. Fixed anatomy, top to bottom:

```
<emoji> <title — channel> — <headline>
<emoji> <segment> · <emoji> <segment> · 💵 <b>amount</b> · 👤 <actor>
🕓 <H:i> · <d/m/Y>
```

Approved by the owner on 2026-11-04 (variant 1 of four samples shown on the
channel):

```
💰 دفعة عبر التطبيق — ام سليم العلي تذكير
🧾 #19598 · 📅 10/2026 · 💵 15.00 $ · 👤 محل الصفدي
🕓 16:12 · 04/11/2026
```

The channel lives in the title: `invoice_paid` → «دفعة عبر الويب»,
`mobile_invoice_paid` → «دفعة عبر التطبيق».

Hard limits: three lines, one segment per fact joined by ` · `, each value
trimmed to 46 chars, the amount bold and always on the details line.

### R2 — All eighteen call sites migrated, same facts

No event is added and none is removed. Every fact currently in the text must
survive the change; only the layout changes. Event catalogue (call sites as they
exist today):

| # | Event key | Call site | Required fields | Link target |
|---|---|---|---|---|
| 1 | `invoice_paid` | `InvoiceController.php:701` | client, invoice #, amount, due date, days late | invoice details |
| 2 | `invoice_paid` | `InvoiceController.php:805` | client, invoice #, amount, due date, days late | invoice details |
| 3 | `invoice_paid` | `ClientController.php:690` | client, invoice #, amount + type, period/note | invoice details |
| 4 | `mobile_invoice_paid` | `SecureMobilePaymentService.php:198` | client, invoice #, period, amount, collector | invoice details |
| 5 | `invoice_balance_edited` | `InvoiceController.php:717` | invoice #, client, before, after, delta | invoice details |
| 6 | `invoice_balance_edited` | `InvoiceController.php:821` | same | invoice details |
| 7 | `invoice_created` | `ClientController.php:600` | invoice #, client, amount | invoice details |
| 8 | `invoice_deleted` | `InvoiceController.php:571` | invoice #, client, amount | client page |
| 9 | `invoice_redone` | `InvoiceController.php:1040` | invoice #, client, amount | invoice details |
| 10 | `client_added` | `ClientService.php:98` | client, subscription type, price | client page |
| 11 | `client_updated` | `ClientController.php:398` | client | client page |
| 12 | `client_deleted` | `ClientController.php:426` | client | — (deleted) |
| 13 | `client_status_changed` | `ClientController.php:722` | client, old → new status | client page |
| 14 | `expense_added` | `MasrofatController.php:149` | description, amount | expenses page |
| 15 | `transfer_created` | `AccountTransferController.php:334` | amount, from account, to account | accounts page |
| 16 | `transfer_redone` | `AccountTransferController.php:491` | amount, from account, to account | accounts page |
| 17 | `admin_added` | `UsersController.php:188` | name, role, email | users page |
| 18 | `overdue_reminder` | `SendOverdueReminders.php:75` | invoice #, client, amount, due date | invoice details |

Note: the client-import message is a `log_helper` audit row only — it is **not**
a Telegram event today and stays that way. `overdue_reminder` is a scheduled
command, not a user action; it keeps its own wording but uses the same builder.

### R3 — Every dynamic value is escaped

All interpolated values pass through `htmlspecialchars(..., ENT_QUOTES |
ENT_SUBSTITUTE, 'UTF-8')`. `parse_mode` stays `HTML`. Markup the builder itself
adds (`<b>`) is never escaped.

### R4 — No links, no buttons (owner decision, 2026-11-04)

The message must be readable on its own. No inline keyboard, no URL inside the
text, nothing to tap: the owner rejected the «فتح السجل» button after seeing it
on the channel («لم يعجبني فكرة زر فتح السجل … بدون أن أذهب إلى أي link»). The
record ids, client name, amount and dates already printed are the record
reference. The «Link target» column of the catalogue above is therefore
historical and must not be implemented.

### R5 — Failures are visible

When the Telegram API answers non-2xx or the request throws:

- log a `warning` with the event key and Telegram's `description`;
- retry **once** (short backoff), then give up;
- never throw into the caller: the financial write must stay committed and the
  HTTP response unchanged.

Cost of the added latency: the send must remain **after** `DB::commit()` /
outside the transaction at every site (verify per site, do not assume).

### R6 — Gating unchanged

`telegram_enabled` (master) and `telegram_notify_<event>` (per event) keep their
current semantics: absent key means enabled, explicit `0` disables. Recipient is
`telegram_chat_id`. No new `app_config` key, no migration, no settings-screen
change.

## Non-goals

- No digest, no batching, no quiet hours, no message throttling (owner decision
  2026-11-04: every operation, immediately).
- No new events; the missing write paths (`revenues`, `masrofat.update/destroy`,
  invoice generation, roles, settings, WhatsApp campaigns) are explicitly out of
  scope for this spec and stay out until requested.
- No change to `log_helper` audit rows, FCM/OneSignal push payloads, or any
  WhatsApp path.
- No schema change, no new table, no migration.
- No change to the Telegram backup command or its `sendDocument` path.
- No deep links, no inline keyboards, no reply_markup anywhere.

## Evidence

- Call sites enumerated by grepping `sendTelegramNotification(` across `app/`
  (18 hits, excluding the helper definition).
- Message inventory extracted from each closure's `sprintf()` block.
- Volume measured on the imported production database (`tahseel_prod_20261104`):
  payments `tbl_revenues` 36.8/day average over 30 days, peak 94 (2026-11-03),
  63 today; transfers 1.3/day; expenses 0.3/day.
- `TelegramApiClient::sendMessage()` confirmed to post `parse_mode=HTML` with no
  escaping upstream; helper confirmed to `return false` without logging.

## Safety

- Text-only change at the send boundary. No financial rule, no balance, no
  invoice status, no queue behaviour is touched.
- Tests must never reach `api.telegram.org`: the API client is stubbed and the
  payload asserted in memory.
- One live verification message per event family is sent to the `ttt` test
  channel only after the suite is green and with the owner watching.
