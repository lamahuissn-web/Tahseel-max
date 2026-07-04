# Feature Specification: Unified WhatsApp Message System

**Feature Branch**: `feature/mobile-invoices`

**Created**: 2026-07-04 | **Updated**: 2026-07-04

**Status**: Draft

**Input**: Audit of existing WhatsApp system — two different message formats (reminder vs receipt), no branding consistency, Arabic month names mixed with numeric format.

---

## Problem Statement

The WhatsApp messaging system in Tahseel has **two independent message builders** producing **different formats**:

| Aspect | WhatsAppMessageBuilder (Reminders) | PaymentReceiptNotifier (Receipt) |
|--------|-----------------------------------|----------------------------------|
| Branding | ❌ No MegaNet header | ✅ 🌐 MegaNet |
| Month format | Arabic names + (MM-YYYY) | ✅ MM/YYYY |
| Customer name | `{name}` placeholder | ✅ 👤 اسم المشترك |
| Footer | Generic thank you | ✅ MegaNet branding |
| Template source | DB (app_config) | Hardcoded |

**Goal**: Unify both message types under a single brand identity while keeping the template editable from settings.

---

## User Stories

### User Story 1 — Unified Brand Template (Priority: P1)

كمستخدم، بدي **كل رسائل الواتساب** (تذكير + إيصال) يكون عندهم **هوية واحدة**:
- 🌐 MegaNet في البداية
- 👤 اسم المشترك
- ━━━ separator lines
- شكراً لاختياركم MegaNet 🌹 في النهاية
- أرقام إنجليزية (0-9)
- صيغة MM / YYYY للشهور

**Acceptance Criteria:**
1. Reminder messages start with 🌐 MegaNet header
2. Reminder messages include customer name
3. Reminder messages use MM/YYYY numeric format (no Arabic month names)
4. Reminder messages end with MegaNet branding footer
5. Receipt messages keep their current format (already correct)

---

### User Story 2 — Editable Template from Settings (Priority: P1)

كمستخدم (مدير)، بدي **أقدر أعدّل قالب التذكير** من صفحة الإعدادات بدون ما أمسح كود.

**Acceptance Criteria:**
1. Default template stored in `WhatsAppMessageBuilder::defaultTemplate()`
2. User can override template via `app_config → whatsapp_message_template`
3. Preview shows the template with real variables
4. If user deletes custom template → falls back to default
5. Template supports these placeholders: `{name}`, `{total_amount}`, `{invoice_details_list}`

---

### User Story 3 — Consistent Invoice List Format (Priority: P2)

قائمة الفواتير تكون **موحّدة** بين التذكير والإيصال.

**Acceptance Criteria:**
1. Each invoice line: `❌ MM / YYYY      $amount`
2. Invoices sorted chronologically
3. Total at the bottom
4. No invoice numbers shown to customers
5. No Arabic month names

---

### User Story 4 — Remove Duplicate Month Arrays (Priority: P2)

نظّف الكود من **نظامين للأشهر العربية**:
- WhatsAppMessageBuilder: "كانون الثاني, شباط..." (Levantine)
- WhatsAppRemindersCommand: "يناير, فبراير..." (Standard)

---

## Requirements

### Functional Requirements

- **FR-001**: System MUST have one default template for reminder messages
- **FR-002**: Template MUST support `{name}`, `{total_amount}`, `{invoice_details_list}` placeholders
- **FR-003**: Default template MUST include MegaNet branding (header + footer)
- **FR-004**: Invoice list in reminders MUST use `❌ MM / YYYY      $amount` format
- **FR-005**: Invoice list MUST NOT show invoice numbers
- **FR-006**: Month format MUST be numeric MM/YYYY (no Arabic names)
- **FR-007**: User MUST be able to customize template from WhatsApp settings page
- **FR-008**: System MUST fall back to default template if custom is empty/deleted
- **FR-009**: Preview MUST show template rendered with realistic sample data
- **FR-010**: Receipt messages (PaymentReceiptNotifier) MUST remain unchanged

### Non-Functional

- **NFR-001**: Backward compatible — existing `app_config` keys unchanged
- **NFR-002**: WhatsAppMessageBuilder methods signature unchanged

---

## Proposed Default Template

```
🌐 MegaNet

👤 اسم المشترك: {name}

📋 لديك فواتير مستحقة بإجمالي {total_amount}$.

📄 تفاصيل الفواتير المستحقة:
{invoice_details_list}

━━━━━━━━━━━━━━━━━━

⚠️ يرجى التكرم بتسديد الرصيد المستحق في أقرب وقت ممكن.
إذا كنت قد سددت هذا المبلغ مؤخراً، يرجى تجاهل هذه الرسالة.

شكراً لاختياركم MegaNet 🌹
```

With invoice details formatted as:
```
❌ 05 / 2026      $15.00
❌ 06 / 2026      $15.00
```

---

## Files to Modify

### Core Changes
| File | Change |
|------|--------|
| `app/Services/WhatsAppMessageBuilder.php` | Update default template, update `buildInvoiceDetailsList()` to use numeric format, remove Arabic month names |
| `app/Services/WhatsAppRemindersCommand.php` | Remove duplicate Arabic months array (use message builder only) |

### Preview / Settings
| File | Change |
|------|--------|
| `app/Http/Controllers/Admin/WhatsAppSettingsController.php` | Update `preview()` to use new template format in sample data |

### Receipt (No Changes)
| File | Reason |
|------|--------|
| `app/Services/WhatsApp/PaymentReceiptNotifier.php` | ✅ Already correct — leave unchanged |

---

## Out of Scope

- ❌ Multi-language templates (Arabic only for now)
- ❌ Template variables beyond `{name}`, `{total_amount}`, `{invoice_details_list}`
- ❌ Scheduled message preview in UI
- ❌ Media attachments (PDF invoices)
