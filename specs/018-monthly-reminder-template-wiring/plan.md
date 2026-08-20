# Plan — 018 Monthly Reminder Template Wiring

## Tech stack
Laravel 10, existing `WhatsAppService` + `ZernioService`, `whatsapp_database` queue.
Meta template `monthly_reminder_v1` (UTILITY, ar, 5 params) proxied via Zernio.

## Data flow (target)
```
trigger (calendarSend / ReminderService::enqueueReminders)
   → MonthlyReminderNotifier::notify(clientId)
        builds 5 template_variables  (deleted_at IS NULL)
        creates whatsapp_message_logs row:
            template_type = 'monthly_reminder'
            template_variables = [...]
            status = 'pending'
   → WhatsAppMessageDispatcher::dispatch() → queue
   → SendWhatsAppMessage::deliver()
        if template_type === 'monthly_reminder' → template_name = config('zernio.reminder_template')
        → WhatsAppService::send(phone, message, [template_name, template_language='ar', template_variables])
        → ZernioService::sendSmart() → Meta template (business-initiated, no 24h window needed)
```

## Files
1. **NEW** `app/Services/WhatsApp/MonthlyReminderNotifier.php`
   - `notify(int $clientId): string` — mirrors `PaymentReceiptNotifier` shape.
   - Queries `tbl_invoices` for client where `status in (unpaid, partial)` and `deleted_at IS NULL`.
   - Splits into subscription months (numeric, sorted, `MM/YYYY` for `{{2}}` = soonest;
     comma-joined for `{{3}}`) and services (`{{4}}` desc+amount).
   - `{{5}}` = sum(amount) over all unpaid (excluding nothing; it's a reminder, not a receipt).
   - Returns `'queued' | 'not_applicable' | 'retry'`.
   - Builds free-text `message` fallback (kept for OpenWA / pre-approval safety) but
     ALWAYS also stores `template_variables`.
2. **EDIT** `app/Jobs/SendWhatsAppMessage.php` `deliver()`
   - Replace the binary `receipt ? receipt_template : reminder_template` with an explicit
     map: `receipt` → receipt_template; `monthly_reminder` → reminder_template;
     everything else → (no template, free-text).
3. **EDIT** `app/Http/Controllers/Admin/WhatsAppControlCenterController.php` `calendarSend()`
   - For the multi-client enqueue branch, call `MonthlyReminderNotifier` (when driver is
     zernio AND reminder template configured) to populate `template_variables` and set
     `template_type = 'monthly_reminder'`.
4. **EDIT** `app/Services/WhatsApp/ReminderService.php` `enqueueReminders()`
   - Same: when zernio + reminder_template set, build variables and `template_type='monthly_reminder'`.
5. **EDIT** `.env` (and `.env.example` if present)
   - `ZERNIO_REMINDER_TEMPLATE=monthly_reminder_v1`
6. **NEW** `tests/Feature/MonthlyReminderNotifierTest.php`
   - `Http::fake()` Zernio; fixture client with mixed invoices; assert variable array +
     log row + job mapping.

## Mapping table
| template_type        | template_name                          | transport         |
|----------------------|---------------------------------------|-------------------|
| receipt              | payment_receipt_v4                     | template (Meta)   |
| monthly_reminder     | ZERNIO_REMINDER_TEMPLATE              | template (Meta)   |
| collector_reminder   | (none)                                | free-text         |
| reminder (legacy)    | (none)                                | free-text         |

## Risks
- `monthly_reminder_v1` currently `PENDING` at Meta. Wiring is inert until approval;
  setting `ZERNIO_REMINDER_TEMPLATE` only affects `monthly_reminder` logs, so no
  pre-approval breakage.
- OpenWA driver: `usesZernio()` false → reminder still sent free-text (unchanged).
