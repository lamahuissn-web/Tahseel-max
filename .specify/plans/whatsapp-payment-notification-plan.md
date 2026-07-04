# Implementation Plan: WhatsApp Payment Notification

**Branch**: `feature/mobile-invoices` | **Date**: 2026-07-04 | **Spec**: `.specify/specs/whatsapp-payment-notification.md`

## Summary

After a successful invoice payment (pay_invoice), send a WhatsApp message to the client containing:
（1） confirmation of the paid invoice （2） a clear list of any remaining unpaid invoices （3） total outstanding balance. The WhatsApp send is non-blocking — payment succeeds regardless.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 10

**Primary Dependencies**: WhatsAppService （existing, OpenWA-based）, Laravel Log facade

**Storage**: Database reads only — no schema changes needed

**Testing**: Manual test via OpenWA + real WhatsApp send; verify via logs

**Target Platform**: OpenWA API （192.168.0.75:2785）

**Project Type**: Laravel service + controller modification

**Performance Goals**: WhatsApp send timeout < 5 seconds, non-blocking to payment flow

**Constraints**: 
- Do NOT modify InvoiceController@pay_invoice signature or return type
- Do NOT create new DB tables or columns
- Do NOT add new routes
- Payment MUST succeed even if WhatsApp is down （Principle I）

**Scale/Scope**: Single payment action → WhatsApp message

## Constitution Check

✅ **Non-Blocking Notifications** — Wrapped in try-catch, no rollback
✅ **Full Financial Transparency** — Paid + unpaid invoices in message
✅ **Graceful Degradation** — No WhatsApp number? Skip silently
✅ **Single Responsibility** — New dedicated class, controller gets one line
✅ **Observable by Default** — Every send/skip logged
✅ **No DB changes** — Reads only, no migration needed

## Project Structure

### New file
```text
app/
└── Services/
    └── WhatsApp/
        └── PaymentReceiptNotifier.php          # ★ جديد — كل المنطق هنا
```

### Modified file
```text
app/
└── Http/
    └── Controllers/
        └── Admin/
            └── InvoiceController.php           # تعديل — إضافة سطر واحد بعد القبض
```

### NOT modified
```text
app/Services/WhatsAppService.php                # ❌ يستخدم كما هو
database/                                       # ❌ لا تغيير
routes/                                         # ❌ لا تغيير
```

## Implementation Phases

### Phase 1: Create PaymentReceiptNotifier
1. Create `app/Services/WhatsApp/PaymentReceiptNotifier.php`
2. Method `notify(Invoice $invoice): void`
3. Logic:
   - Get client from invoice->client relationship
   - Get client phone number （whatsapp or phone field）
   - If no phone number → log info + return
   - Query unpaid invoices for this client （excluding current invoice）
   - Build message string from template （Arabic + emojis）
   - Call `app（WhatsAppService::class）->sendMessage（$phone, $message）`
   - Wrap in try-catch, log success/warning/error

### Phase 2: Modify InvoiceController@pay_invoice
1. After `$invoice->update（['status' => 'paid', 'paid_date' => now（）]）` or equivalent
2. Add: `app（PaymentReceiptNotifier::class）->notify（$invoice）;`
3. No try-catch in controller — all error handling inside notifier

### Phase 3: Verify
1. Check syntax （PHP artisan t:l or composer dump-autoload）
2. Manual test: pay an invoice for a client with WhatsApp number
3. Check logs for send confirmation
4. Test: pay for client without WhatsApp number — verify silent skip
