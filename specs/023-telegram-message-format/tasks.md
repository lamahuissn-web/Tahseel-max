# Tasks 023: Telegram Notification Message Format

Test-first. Every task's test name is given so the suite reads as the spec.
Nothing is committed to `main`; work stays on `feature/023-telegram-message-format`.

## Phase 0 — RED

- [ ] T0.1 Write `tests/Unit/TelegramMessageTest.php`
  - `test_anatomy_is_title_separator_fields_amount_footer`
  - `test_every_dynamic_value_is_html_escaped` (`&`, `<`, `>`, `"`, emoji)
  - `test_null_and_empty_fields_are_dropped`
  - `test_amount_line_is_bold_and_keeps_two_decimals`
  - `test_long_values_are_trimmed_to_the_line_budget`
  - `test_link_renders_as_an_inline_button`
  - `test_missing_title_throws_instead_of_sending`
- [ ] T0.2 Write `tests/Feature/TelegramNotificationFormatTest.php` skeleton:
  fake the API client in the container, capture payloads, one test per event key.
- [ ] T0.3 Write the failure-path test: `400 can't parse entities` → one retry,
      one `warning` log line, `false` return, originating response unchanged.
- [ ] T0.4 Run: `phpunit --filter 'TelegramMessage|TelegramNotificationFormat'`
      and confirm it fails for the expected reason (class missing), not a typo.

## Phase 1 — builder + payments

- [ ] T1.1 Create `app/Services/Telegram/TelegramMessage.php` (EVENT_META for all
      fifteen keys, escape, field/amount/actor/at/link, `toPayload()`, `send()`).
- [ ] T1.2 `TelegramApiClient::sendMessage()` accepts `?array $replyMarkup` and
      forwards `reply_markup`; other methods untouched.
- [ ] T1.3 `sendTelegramNotification()` gains the `TelegramMessage` branch and
      escapes raw strings on the legacy branch.
- [ ] T1.4 Migrate `invoice_paid` ×3 (InvoiceController:701, :805,
      ClientController:690) and `mobile_invoice_paid` (SecureMobilePaymentService:198).
- [ ] T1.5 Assert send stays outside the transaction at each migrated site
      (`DB::commit()` before the call) and record the line numbers in the PR body.
- [ ] T1.6 Green: unit suite + the four payment feature tests.

## Phase 2 — invoices + clients

- [ ] T2.1 Migrate `invoice_balance_edited` ×2 (717, 821), `invoice_created` (600),
      `invoice_deleted` (571), `invoice_redone` (1040).
- [ ] T2.2 Migrate `client_added` (ClientService:98), `client_updated` (398),
      `client_deleted` (426, no button), `client_status_changed` (722).
- [ ] T2.3 Each event test asserts: title, the facts the old text carried, the
      link target, and that no `null`/`-` leaks into the text.
- [ ] T2.4 Green: full `TelegramNotificationFormat` suite.

## Phase 3 — money + system

- [ ] T3.1 Migrate `transfer_created` (334), `transfer_redone` (491),
      `expense_added` (149), `admin_added` (188), `overdue_reminder`
      (SendOverdueReminders:75).
- [ ] T3.2 Confirm no call site outside the migrated eighteen still passes a raw
      string (`grep -rn "sendTelegramNotification(" app/` → 18 hits, all typed).
- [ ] T3.3 Green: whole suite.

## Phase 4 — verification

- [ ] T4.1 Focused: `phpunit --filter 'TelegramMessage|TelegramNotificationFormat'`.
- [ ] T4.2 Full suite as `www-data`; `php artisan view:cache`; no new log errors.
- [ ] T4.3 Send one live message per family to `ttt` (payment, invoice, client,
      money, system) and screenshot/confirm with the owner before merge.
- [ ] T4.4 Independent fresh-context read-only audit, then approval to commit/push.

## Deferred (explicitly not in this spec)

- Notifications for `revenues` create/update/delete, `masrofat` update/delete,
  monthly invoice generation, roles/permissions, settings saves, WhatsApp
  campaigns, SAS actions — owner asked for the existing eighteen only.
- Digest / quiet hours / throttling — rejected by the owner on 2026-11-04.
