# Tasks: WhatsApp Payment Notification

**Input**: Spec from `.specify/specs/whatsapp-payment-notification.md`
**Plan**: `.specify/plans/whatsapp-payment-notification-plan.md`

---

## Phase 1: Create PaymentReceiptNotifier Service

- [ ] T001 [P] [US1] Create `app/Services/WhatsApp/PaymentReceiptNotifier.php` with `notify($invoice)` method
- [ ] T002 [US1] Implement client phone lookup logic (whatsapp/phone field fallback)
- [ ] T003 [US1] Implement unpaid invoices query (exclude current invoice)
- [ ] T004 [US1] Build message template with Arabic text + emojis:
  - Paid invoice details
  - Unpaid invoices list (if any)
  - Total outstanding balance
  - "All paid" message if nothing remaining
- [ ] T005 [US1] Implement try-catch wrapper with graceful degradation logging
- [ ] T006 [US1] Add info logs for successful sends, warning logs for skips/no-number

## Phase 2: Integrate with InvoiceController

- [ ] T007 [US1] Add single line in `InvoiceController@pay_invoice` after successful payment:
  `app(PaymentReceiptNotifier::class)->notify($invoice);`

## Phase 3: Verify

- [ ] T008 [US1] Run syntax check: `php artisan t:l` (or `composer dump-autoload`)
- [ ] T009 [US1] Manual test: Pay invoice for client with WhatsApp number → verify message
- [ ] T010 [US1] Manual test: Pay invoice for client without WhatsApp → verify silent skip in logs

---

## Execution Order

```
T001 ─→ T002 ─→ T003 ─→ T004 ─→ T005 ─→ T006 ─→ T007 ─→ T008 ─→ T009 ─→ T010
```

All sequential (no parallel tasks since it's a single logical flow).

**Commit message**: `feat: send WhatsApp receipt notification on payment with unpaid invoices list`
