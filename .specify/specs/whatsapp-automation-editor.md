# Feature Specification: Automation Rules Editor

**Feature Branch**: `feature/whatsapp-control-center`

**Created**: 2026-07-12 | **Updated**: 2026-07-12

**Status**: Draft

**Input**: Kira wants to upgrade the simple automation toggle page to a full rules editor with per-rule scheduling, templates, and configuration.

---

## Problem Statement

The current automation page at `ar/admin/whatsapp/automation` is minimal:

| Current Limitation | Impact |
|--------------------|--------|
| Only 1 rule (reminder) | Can't automate receipts, disconnection notices, or custom messages |
| Global toggle only (`whatsapp_auto_enabled`) | Can't enable/disable rules independently |
| No schedule settings | Cron runs at fixed time — admin can't change it from UI |
| No template binding | Each rule always uses the same template — can't choose |
| No days-of-week control | Can't say "run only on Sat-Wed" |
| Stored as flat `app_config` keys | Hard to extend with per-rule settings |

**Goal**: Transform into a full rules editor with per-rule cards, inline editing, scheduling, template selection, and days-of-week control.

---

## User Stories

### User Story 1 — See All Rules at a Glance

كمشرف، بدي أشوف كل قواعد التشغيل الآلي على شكل **كروت** مرتبة:
- اسم القاعدة
- مفعلة ولا لا (badge)
- وقت التشغيل (مثال: 09:00)
- أيام الأسبوع (مثال: كل الأيام)
- القالب المستخدم
- خيارات: تعديل، تشغيل الآن، تفعيل/تعطيل

### User Story 2 — Edit Rule Schedule and Template

بدي أضغط على **تعديل** لأي قاعدة ويظهر لي فورم داخل الكارد نفسه (inline edit) أقدر أغيّر منه:
- وقت التشغيل (time picker)
- أيام الأسبوع (checkboxes)
- القالب (dropdown of WhatsApp templates)
- عدد الأيام قبل/بعد (مثلاً: قبل القطع بـ ٣ أيام)

### User Story 3 — Enable/Disable Individual Rules

بدي أقدر أطفئ قاعدة لحالها (مثلاً أوقف إيصال الدفع) بدون ما تأثر على باقي القواعد.

### User Story 4 — Run Rule on Demand

بدي أقدر أشغل أي قاعدة يدويًا من الضغطة (زي ما هو موجود حالياً).

### User Story 5 — Multiple Rule Types

القواعد المطلوبة:
| القاعدة | Command | الوصف |
|---------|---------|-------|
| 📦 تذكير قبل القطع | `whatsapp:reminders` | تذكير الزبائن قبل فصل الخدمة |
| 📄 إيصال الدفع | `whatsapp:receipt` | إرسال إيصال بعد الدفع |
| 🔌 إشعار القطع | `whatsapp:disconnection` | إشعار عند فصل الخدمة |
| ✉️ رسالة مخصصة | `whatsapp:custom` | إرسال رسالة مخصصة بجدول |

---

## Design

### Layout: Cards with Inline Editing

```
┌──────────────────────────────────────────────────┐
│  🤖 Automation Rules                             │
│  إدارة قواعد التشغيل الآلي للواتساب              │
├──────────────────────────────────────────────────┤
│                                                  │
│  ┌─ 📦 تذكير قبل القطع ─────────────────── 🟢 ─┐ │
│  │  ⏰ 09:00  •  📅 كل الأيام                   │ │
│  │  📝 تذكير فاتورة                             │ │
│  │  🔔 قبل القطع بـ 3 أيام                      │ │
│  │  ─────────────────────────────────           │ │
│  │  [✏️ تعديل]  [▶️ تشغيل الآن]  [🔘 تعطيل]     │ │
│  │                                              │ │
│  │  ▼ [EXPANDED] فورم التعديل:                  │ │
│  │  ┌─ وقت التشغيل: [⏰ 09:00]                  │ │
│  │  │  أيام: [☑ س] [☑ ح] [☑ ن] [☐ ث] [☐ ر] ...│ │
│  │  │  القالب: [تذكير فاتورة ▼]                 │ │
│  │  │  قبل/بعد: [3] أيام                        │ │
│  │  │  [💾 حفظ] [✖ إلغاء]                       │ │
│  │  └────────────────────────────────           │ │
│  └──────────────────────────────────────────────┘ │
│                                                  │
│  ┌─ 📄 إيصال الدفع ───────────────────── 🔴 ─┐  │ │
│  │  ⏰ 12:00  •  📅 س, ح, ن                    │ │
│  │  📝 إيصال دفع                                │ │
│  │  [✏️ تعديل]  [🔘 تفعيل]                      │ │
│  └──────────────────────────────────────────────┘ │
│                                                  │
│  ┌─ 🔌 إشعار القطع ──────────────────── 🔴 ─┐  │ │
│  │  ...                                       │ │
│  └──────────────────────────────────────────────┘ │
│                                                  │
│  ┌─ ✉️ رسالة مخصصة ─────────────────── 🔴 ─┐   │ │
│  │  ...                                       │ │
│  └──────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────┘
```

### Data Storage

**Option: JSON column in `app_config`** (no migration needed)

```json
{
  "whatsapp_remind_before": {
    "enabled": true,
    "time": "09:00",
    "days": [0,1,2,3,4,5,6],
    "template": "reminder",
    "days_before": 3,
    "command": "whatsapp:reminders"
  },
  "whatsapp_receipt": {
    "enabled": false,
    "time": "12:00",
    "days": [0,1,2],
    "template": "receipt",
    "days_after": 0,
    "command": "whatsapp:receipt"
  },
  "whatsapp_disconnection": {
    "enabled": false,
    "time": "08:00",
    "days": [0,1,2,3,4,5,6],
    "template": "disconnection",
    "days_before": 1,
    "command": "whatsapp:disconnection"
  },
  "whatsapp_custom": {
    "enabled": false,
    "time": "10:00",
    "days": [0,1,2,3,4,5,6],
    "template": "custom",
    "command": "whatsapp:custom"
  }
}
```

A single `app_config` key `whatsapp_automation_rules` stores the JSON blob.

### Backend API

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/admin/whatsapp/automation/{id}/toggle` | Toggle single rule on/off |
| POST | `/admin/whatsapp/automation/{id}/run` | Run rule immediately |
| POST | `/admin/whatsapp/automation/save` | Save all rules config |
| POST | `/admin/whatsapp/automation/{id}/save` | Save single rule config |

### Files Changed

| File | Change |
|------|--------|
| `app/Http/Controllers/Admin/WhatsAppControlCenterController.php` | Updated `automation()`, `toggleAutomationRule()`, `runAutomationRule()`, new `saveAutomationConfig()` |
| `resources/views/dashbord/whatsapp/automation.blade.php` | Complete rewrite — card layout with inline editing |
| `config/whatsapp.php` (or `app_config`) | New JSON config structure |

---

## Acceptance Criteria

1. ✅ Page shows 4 rule cards: Reminder, Receipt, Disconnection, Custom
2. ✅ Each card shows: status badge, time, days, template, action buttons
3. ✅ Click "🔘 تعطيل/تفعيل" toggles individual rule without affecting others
4. ✅ Click "✏️ تعديل" expands inline edit form within the card
5. ✅ Inline form has: time picker, day checkboxes, template dropdown, days-before input
6. ✅ "💾 حفظ" saves and collapses; "✖ إلغاء" collapses without saving
7. ✅ "▶️ تشغيل الآن" runs the rule command immediately
8. ✅ Only one card can be in edit mode at a time
9. ✅ Responsive — cards stack on mobile
10. ✅ All existing automation functionality preserved

---

## Risks

- JSON config size — 4 rules with full settings is small, no concern
- Old `whatsapp_auto_enabled` — must migrate: new code reads per-rule `enabled`, fallback for old single key
- Commands for receipt/disconnection/custom may not exist yet — we register the config but they'll run only when commands are created
