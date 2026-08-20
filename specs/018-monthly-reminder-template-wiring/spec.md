# Feature 018 — Monthly Reminder Template Wiring (monthly_reminder_v1)

## Goal
Wire the Meta-approved `monthly_reminder_v1` WhatsApp template into Tahseel's
business-initiated reminder path so reminders are sent as a Meta-APPROVED template
(outside the 24h customer-service window) instead of free-form text that Meta
currently rejects. The wiring MUST be ready-to-fire the moment Meta flips
`monthly_reminder_v1` from `PENDING` to `APPROVED`, with no further code change.

This closes Phase 2c of the `spike/zernio-whatsapp-adapter` branch.

## Requirements
- A dedicated `MonthlyReminderNotifier` service builds the 5 `monthly_reminder_v1`
  template variables per client and stores them on `whatsapp_message_logs.template_variables`.
  Variables (Meta template body, Arabic UTILITY, 5 params):
  - `{{1}}` subscriber name (client name)
  - `{{2}}` soonest unpaid **subscription** due month as `MM/YYYY`
  - `{{3}}` unpaid subscription months, comma-joined numeric (`7, 8`); `لا يوجد` if none
  - `{{4}}` unpaid services: `desc ($amount)` joined by `, `; `لا يوجد` if none
  - `{{5}}` total outstanding across ALL non-deleted unpaid invoices for the client
- Source query MUST always apply `deleted_at IS NULL` (same lesson as payment_receipt_v4).
- `SendWhatsAppMessage::deliver()` MUST map a distinct `template_type = 'monthly_reminder'`
  to `config('zernio.reminder_template')` so the reminder template is used WITHOUT
  hijacking `collector_reminder` (which stays free-text) or plain `reminder` logs.
- All reminder trigger paths MUST populate `template_variables` and use `template_type='monthly_reminder'`
  when the Zernio driver is active and `ZERNIO_REMINDER_TEMPLATE` is configured:
  - `WhatsAppControlCenterController::calendarSend()` (admin calendar multi-select)
  - `ReminderService::enqueueReminders()` (automation/rule enqueue)
  - `CollectorReminderSendCommand` stays OUT of scope (collectors are staff, not subscribers;
    it keeps `collector_reminder` free-text by design)
- `.env`: set `ZERNIO_REMINDER_TEMPLATE=monthly_reminder_v1` (the live switch).
- When `ZERNIO_REMINDER_TEMPLATE` is empty, behavior is unchanged (free-text / text-only),
  preserving backward compatibility with OpenWA driver.

## Non-goals / safety
- NO auto-scheduling. Reminders remain manual/admin-initiated (Kira's stated preference).
- NO migration: `template_variables` JSON column already exists
  (migration 2026_08_17_135609).
- NO production push. Work stays on `spike/zernio-whatsapp-adapter`, committed only.
- NO change to `payment_receipt_v4` (receipt) path.
- NO change to `collector_reminder` free-text behavior.

## Acceptance criteria
- Unit tests prove `MonthlyReminderNotifier` builds the exact 5-variable array for a
  fixture client (subscription months + services + total), applying `deleted_at IS NULL`.
- Unit tests prove the job maps `template_type='monthly_reminder'` to the configured
  reminder template name and passes `template_variables` to `WhatsAppService::send()`.
- `php artisan config:show zernio` reflects `reminder_template = monthly_reminder_v1`.
- A dry enqueue (no real send) writes a `whatsapp_message_logs` row with
  `template_type='monthly_reminder'` and non-empty `template_variables`.
- Existing WhatsApp feature suite stays green.
- `php -l` clean on all touched files; no config-cache corruption.
