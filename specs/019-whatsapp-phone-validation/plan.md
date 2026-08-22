# Plan — Feature 019

## Approach
Choke-point + gates. One validator used at every entry point, plus a
delivery-time backstop inside the job (precedent: kill-switch re-verify).

## Components
1. `app/Services/WhatsApp/WhatsAppPhoneValidator.php` (new, static, pure).
2. `app/Services/WhatsApp/PaymentReceiptNotifier.php` — compute `normalize()`
   early; before dispatch decide status: valid -> pending + e164; invalid ->
   failed + error, skip `dispatcher->dispatch()`.
3. `app/Services/WhatsApp/MonthlyReminderNotifier.php` — gate on validity;
   store e164.
4. `app/Services/WhatsApp/ReminderService.php` — both empty-guard sites become
   validity guards.
5. `app/Http/Controllers/Admin/WhatsAppControlCenterController.php` — manual
   send validation + bulk/calendar skip logic.
6. `app/Jobs/SendWhatsAppMessage.php` — backstop after kill-switch check.

## Data model
No migrations. Uses existing `status=failed` + `text error` columns.

## Test strategy (TDD)
- Unit: `tests/Unit/WhatsAppPhoneValidatorTest.php` — truth table incl.
  `961000000`, `+961 3 123456`, `03123456`, `70123456`, `96170123456`,
  letters, empty, `00…`, too short/long, foreign number.
- Feature: `tests/Feature/WhatsAppPhoneValidationTest.php` — receipt failed-row
  + no dispatch; monthly skip; deliver backstop. Mock dispatcher/Http (global
  Queue::fake safety net stays).
- Regression: full existing WhatsApp test battery must stay green.

## Risks
- Over-strict rejection could block a legitimately valid number format not yet
  seen (dev DB is uniformly `+961XXXXXXXXX`). Mitigation: prefix list constant,
  unit-tested, easy to extend; failures are loud (logged/visible), never silent.
