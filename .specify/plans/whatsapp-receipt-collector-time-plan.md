# Implementation Plan: WhatsApp Receipt - Add Collector Name & Payment Time

**Branch**: feature/mobile-invoices | **Date**: 2026-07-05 | **Spec**: .specify/specs/whatsapp-receipt-collector-time.md

## Summary
Add the employee name who collected the payment and the exact date+time of payment to the WhatsApp receipt message.

## Technical Context
- **Language**: PHP 8.3, Laravel 10
- **File to modify**: app/Services/WhatsApp/PaymentReceiptNotifier.php
- **Data source**: tbl_revenues table (collected_by, received_at fields)
- **No migration needed** - data already exists

## Constitution Check
- ✅ Non-Blocking - WhatsApp failure does not affect payment
- ✅ No DB changes - reads only
- ✅ Arabic-first - message updates in Arabic

## Changes

### 1. PaymentReceiptNotifier::notify()
- After getting invoice data, fetch Revenue record:
  `Revenue::where("invoice_id", $invoice->id)->first()`
- If found:
  - Get collector name via `$revenue->collected_by_name` attribute
  - Get payment time from `$revenue->received_at` -> format as `d/m/Y h:i A`
- If not found: set collector name to "النظام", time to current time
- Pass both to buildMessage()

### 2. PaymentReceiptNotifier::buildMessage()
- Add 2 new parameters: string $collectorName, string $paymentTime
- Add to message template before the separator:
  `"🧑 الموظف القابض: {$collectorName}\n"`
  `"⏱ وقت الدفع: {$paymentTime}\n"`
- Replace existing "🗓 تاريخ الدفع: {$paymentDate}" line with the new time format

### 3. Updated Message Section
Before:
```
📅 الاشتراك المسدد: 07 / 2026
💵 المبلغ المدفوع: $15.00

━━━━━━━━━━━━━━━━━━
...
🗓 تاريخ الدفع: 15/07/2026
```

After:
```
📅 الاشتراك المسدد: 07 / 2026
💵 المبلغ المدفوع: $15.00
🧑 الموظف القابض: أحمد علي
⏱ وقت الدفع: 15/07/2026 02:30 PM

━━━━━━━━━━━━━━━━━━
...
```

### 4. Import
- Add `use App\Models\Admin\Revenue;` to PaymentReceiptNotifier.php
