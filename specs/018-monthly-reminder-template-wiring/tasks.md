# Tasks — 018 Monthly Reminder Template Wiring

- [ ] Record feature contract (spec.md), plan (plan.md), non-goals, verification plan.
- [ ] RED: add `tests/Feature/MonthlyReminderNotifierTest.php` — assert 5-variable build
      (subscription months `{{2}}`=`MM/YYYY` soonest, `{{3}}` comma list, `{{4}}` services,
      `{{5}}` total), `deleted_at IS NULL` applied, `template_type='monthly_reminder'` log row,
      and job maps to configured reminder template. Run → must FAIL (class/file absent).
- [ ] GREEN: implement `app/Services/WhatsApp/MonthlyReminderNotifier.php` with `notify(int $clientId)`.
- [ ] GREEN: edit `SendWhatsAppMessage::deliver()` to map `monthly_reminder` → `config('zernio.reminder_template')`
      (explicit map, leave collector_reminder/legacy as free-text).
- [ ] GREEN: wire `calendarSend()` multi-client branch to `MonthlyReminderNotifier` (zernio + template set).
- [ ] GREEN: wire `ReminderService::enqueueReminders()` same condition.
- [ ] GREEN: set `ZERNIO_REMINDER_TEMPLATE=monthly_reminder_v1` in `.env` (and `.env.example`).
- [ ] Run `php -l` on all touched files; run `php artisan test --filter=MonthlyReminder`; run full WhatsApp suite.
- [ ] Real check: `php artisan tinker` — confirm config value + a dry enqueue writes log row with template_variables.
- [ ] Record RED/GREEN evidence; commit to `spike/zernio-whatsapp-adapter` (no push).
