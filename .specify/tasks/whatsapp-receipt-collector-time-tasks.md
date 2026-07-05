# Tasks: WhatsApp Receipt - Add Collector Name & Payment Time

**Spec**: .specify/specs/whatsapp-receipt-collector-time.md
**Plan**: .specify/plans/whatsapp-receipt-collector-time-plan.md

---

## Tasks

- [ ] T001 Modify `notify()` in PaymentReceiptNotifier - fetch Revenue record for this invoice
- [ ] T002 Get collector name via $revenue->collected_by_name (fallback "النظام")
- [ ] T003 Format received_at as d/m/Y h:i A for payment time
- [ ] T004 Update buildMessage() signature - add $collectorName and $paymentTime params
- [ ] T005 Add "🧑 الموظف القابض: {name}" and "⏱ وقت الدفع: {time}" to message template
- [ ] T006 Remove old "🗓 تاريخ الدفع: {date}" line (replaced by new format in receipt header area)
- [ ] T007 Add use statement for Revenue model
- [ ] T008 Verify syntax: php artisan t:l or composer dump-autoload

---

## Execution Order
T001 -> T002 -> T003 -> T004 -> T005 -> T006 -> T007 -> T008

**Commit message**: feat: add collector name and payment time to WhatsApp receipt
