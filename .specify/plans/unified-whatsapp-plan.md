# Implementation Plan: Unified WhatsApp Message System

**Branch**: `feature/mobile-invoices` | **Date**: 2026-07-04 | **Spec**: `.specify/specs/unified-whatsapp-system.md`

## Summary

Unify WhatsApp reminder messages with MegaNet brand identity (same style as PaymentReceiptNotifier), switch to numeric MM/YYYY month format, keep template editable from settings.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 10

**Primary Dependencies**: WhatsAppMessageBuilder (existing), WhatsAppService (existing)

**Storage**: `app_config` DB table (existing key: `whatsapp_message_template`)

**Testing**: Manual — run `php artisan whatsapp:reminders --send` and check WhatsApp

**Constraints**: 
- Do NOT change method signatures of existing public methods
- Do NOT break existing WhatsApp settings UI
- Keep PaymentReceiptNotifier unchanged (already correct)

## Constitution Check

✅ **Brand Consistency** — All messages use MegaNet header/footer
✅ **User Configurable** — Template editable from settings, fallback to default
✅ **Clean Code** — Remove duplicate Arabic month arrays
✅ **Non-Blocking** — No change to payment flow (receipt untouched)

## Project Structure

### Modified files
```text
app/Services/WhatsAppMessageBuilder.php           # ★ Core change
app/Console/Commands/WhatsAppRemindersCommand.php  # Remove duplicate months
app/Http/Controllers/Admin/WhatsAppSettingsController.php  # Update preview sample
```

### Unchanged files
```text
app/Services/WhatsApp/PaymentReceiptNotifier.php   # ✅ Already correct
app/Services/WhatsAppService.php                   # ✅ No change needed
```

## Implementation Phases

### Phase 1: WhatsAppMessageBuilder
1. Update `buildInvoiceDetailsList()` — use `❌ MM / YYYY      $amount` format
2. Remove Arabic month names array (no longer needed)
3. Update `defaultTemplate()` — add MegaNet branding

### Phase 2: WhatsAppRemindersCommand
1. Remove duplicate Arabic months array (line 24-30)
2. Update `displayPreview()` to use MM/YYYY format

### Phase 3: WhatsAppSettingsController
1. Update `preview()` sample data — no invoice numbers, use new format
