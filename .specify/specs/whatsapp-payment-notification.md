# Feature Specification: WhatsApp Payment Notification

**Feature Branch**: `feature/mobile-invoices`

**Created**: 2026-07-04

**Status**: Draft

**Input**: Kira request: "لما نعمل قبض لفاتورة يرسل رسالة واتس لرقم الزبون مع تفاصيل الفاتورة اللي دفعها وإذا عليه فواتير ثانية""

---

## User Stories & Testing

### User Story 1 — إرسال إشعار الدفع مع الفواتير المتبقية (Priority: P1)

كمدير (Kira), بدي لما أضغط "قبض" على فاتورة عميل, يرسل واتساب تلقائياً للعميل فيه:
- تأكيد استلام الدفع للمبلغ
- الفاتورة اللي دفعها (شهرها — قيمتها — تاريخ الدفع)
- إذا عليه فواتير غير مدفوعة, يظهر كل فاتورة مع المبلغ + إجمالي المتبقي
- إذا ما عليه شي, رسالة "كل الفواتير مسددة 🎉"

**Why this priority**: P1 — هذه هي الميزة الأساسية المطلوبة.

**Independent Test**: 
1. افتح صفحة عميل عنده WhatsApp رقم + عنده فاتورة غير مدفوعة
2. ادفع الفاتورة
3. تأكد أن رسالة واتساب وصلت بالتفاصيل الصحيحة

**Acceptance Scenarios**:

1. **Given** عميل عنده واتساب وفاتورة واحدة غير مدفوعة, **When** ندفع الفاتورة, **Then** يرسل واتساب: "✅ تم استلام الدفع" + تفاصيل الفاتورة + "كل الفواتير مسددة 🎉"

2. **Given** عميل عنده 3 فواتير (شهر ٥, ٦, ٧) ودفع شهر ٥, **When** ندفع الفاتورة, **Then** يرسل واتساب: تأكيد دفع شهر ٥ + قائمة بالمتبقي (شهر ٦، شهر ٧) + إجمالي المتبقي

3. **Given** عميل ما عنده رقم WhatsApp, **When** ندفع الفاتورة, **Then** الدفع يتم بدون رسالة خطأ, ونسجل log انه ما في رقم

---

### User Story 2 — عدم تأثير الفشل على عملية القبض (Priority: P1)

**Why this priority**: P1 — أساسي حسب الـ Constitution Principle I (Non-Blocking).

**Acceptance Scenarios**:

1. **Given** OpenWA service مش شغال, **When** ندفع فاتورة, **Then** الدفع يتم بنجاح, ونسجل warning في الـ logs

2. **Given** في خطأ بالـ invoice data, **When** ندفع, **Then** الدفع يتم ونحط error log

---

### Edge Cases

- **Client بدون رقم**: Skip — لا رسالة خطأ, فقط info log
- **OpenWA down**: Warning log + continue, payment successful
- **الفاتورة المدفوعة هي آخر فاتورة**: رسالة "كل الفواتير مسددة" 🎉
- **الفاتورة الوحيدة**: رسالة عادية + "كل الفواتير مسددة 🎉"
- **فواتير متعددة غير مدفوعة**: List itemized مع total outstanding

---

## Requirements

### Functional Requirements

- **FR-001**: System MUST send WhatsApp notification after successful invoice payment
- **FR-002**: Notification MUST include: confirmation message, paid invoice details (month/year, amount)
- **FR-003**: Notification MUST include list of any unpaid invoices with amounts
- **FR-004**: Notification MUST include total outstanding balance if any unpaid invoices exist
- **FR-005**: If no unpaid invoices remain, message MUST say "all invoices paid"
- **FR-006**: WhatsApp sending MUST NOT block or fail the payment transaction
- **FR-007**: Notification logic MUST be in a separate service (not in controller)
- **FR-008**: System MUST log all send attempts (info) and skips (warning)

### Non-Functional Requirements

- **NFR-001**: Message MUST be in Arabic with emojis
- **NFR-002**: Message MUST be readable on mobile WhatsApp screen
- **NFR-003**: System MUST handle concurrent payments without race conditions
- **NFR-004**: Performance: WhatsApp send must timeout within 5 seconds max

### Key Entities

- **Invoice**: id, client_id, amount, month_year, status (paid/unpaid), paid_date
- **Client**: id, name, whatsapp_number, phone
- **WhatsAppService**: sendMessage(to, message) — existing service

---

## Message Template (Arabic)

```
✅ تم استلام الدفع

📋 الفاتورة: [الشهر] — [السنة]
💰 المبلغ: [المبلغ] ل.ل.
📅 تاريخ الدفع: [التاريخ]
🟢 الحالة: مدفوعة ✅

⚠️ فواتير غير مدفوعة:
• [الشهر] — [السنة]: [المبلغ] ل.ل. ❌
• [الشهر] — [السنة]: [المبلغ] ل.ل. ❌

─────────────────
💰 الإجمالي المتبقي: [المجموع] ل.ل.

شكراً لثقتكم 🙏
```

*If no unpaid invoices: replace the unpaid section with `🎉 كل الفواتير مسددة — شكراً لكم`*

---

## Implementation Plan (Brief)

1. Create `app/Services/WhatsApp/PaymentReceiptNotifier.php`
   - Method: `notify(Invoice $invoice): void`
   - Fetches client WhatsApp number
   - Fetches unpaid invoices list
   - Builds message from template
   - Calls WhatsAppService->sendMessage()
   - Wraps in try-catch for graceful degradation

2. Modify `InvoiceController@pay_invoice`
   - Add one line after successful payment:
     `app(PaymentReceiptNotifier::class)->notify($invoice);`

3. Add comprehensive logging

---

## Success Criteria

- **SC-001**: Paying an invoice sends correct WhatsApp within 3 seconds
- **SC-002**: Message shows correct paid/unpaid split per Kira's requirements
- **SC-003**: If WhatsApp fails, payment is still recorded (check logs)
- **SC-004**: No regression in existing payment flow
- **SC-005**: All edge cases handled without user-facing errors

---

## What We WON'T Do (Out of Scope)

- ❌ Media attachments (PDF receipts) — future enhancement
- ❌ Custom message templates per client
- ❌ Scheduled/batch send
- ❌ Delivery receipts / read receipts
- ❌ Multi-language templates (Arabic only for now)

---

## Assumptions

- WhatsAppService->sendMessage() accepts a phone number and text string
- Client phone number format is international (e.g., 9613xxxxxxx)
- Invoice has a `client` relationship to fetch client data
- Unpaid invoices query: Invoice::where('client_id', $clientId)->where('status', '!=', 'paid')
