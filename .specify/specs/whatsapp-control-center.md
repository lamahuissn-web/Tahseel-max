# Feature Specification: WhatsApp Control Center

**Feature Branch**: `feature/whatsapp-control-center`

**Created**: 2026-07-11 | **Updated**: 2026-07-11

**Status**: Draft

**Input**: Discussion with Kira — the current WhatsApp settings page feels incomplete, missing a operations dashboard, template management, targeted sending, message history, automation controls, and queue visibility.

---

## Problem Statement

The current WhatsApp settings page (`settings/whatsapp`) is a **single form-based page** that handles configuration only. It lacks:

| Missing | Impact |
|---------|--------|
| 📊 **Dashboard / Pulse** | No visibility into whether WhatsApp is healthy, how many messages sent, failures |
| 📝 **Template Management** | Message content is hardcoded in PHP — non-technical staff can't edit |
| 📨 **Targeted Sending** | Can only send to "all" (cron) or "one" (client page) — no middle ground |
| 📋 **Message History** | No searchable log of sent/failed messages |
| 🤖 **Automation Controls** | Can't see or toggle schedule rules from UI |
| ⏳ **Queue Visibility** | No idea if messages are pending or backed up |

**Goal**: Transform the simple settings page into a **full WhatsApp operations center** covering monitor → manage → send → track.

---

## User Stories

### User Story 1 — Operations Dashboard (Priority: P1)

كمشرف، بدي أول ما أدخل على **WhatsApp Control Center** أشوف **نظرة شاملة** عن حالة الخدمة:
- اتصال OpenWA شغّال ولا لا
- كم رسالة انبعتت اليوم/هذا الشهر
- كم رسالة فشلت
- كم زبون عندهم رقم واتساب مسجل
- آخر رسالة انبعتت امتى
- حالة الطوارئ (Emergency Kill Switch) مفعّلة ولا لا

**Acceptance Criteria:**
1. Cards showing: Connection Status (🟢/🟡/🔴), Messages Today, Messages This Month, Failures Today
2. Client reachability: "X of Y clients have WhatsApp numbers"
3. Last successful send timestamp
4. Emergency state indicator clearly visible
5. Quick action buttons: Emergency Stop, Restart Service

---

### User Story 2 — Editable Message Templates (Priority: P1)

كمستخدم عادي (مش مبرمج)، بدي **أعدّل نصوص رسائل الواتساب** من الواجهة بدون ما أمسّ الكود:
- شوف معاينة حية للرسالة مع متغيرات
- عدّل النص من textarea
- المتغيرات (`{name}`, `{amount}`, `{date}`) تظهر أزرار تضغط عليها وتضيفها
- اختبر الرسالة بإرسالها لرقمي

**Acceptance Criteria:**
1. List of all message templates (receipt, reminder, disconnection warning, etc.)
2. Each template has: name, preview with sample data, editable body text
3. Variable buttons that insert `{variable}` at cursor position
4. "Send Test" button → prompts for phone number → sends sample
5. Changes saved to database (not hardcoded)

---

### User Story 3 — Targeted Send / Broadcast (Priority: P1)

كمشرف، بدي **أرسل رسائل لمجموعة محددة** من الزبائن — مش الكل ومش واحد واحد:
- **يدوي:** دوّر على زبون بالإسم/الرقم، اختاره، ضيفه للقائمة
- **ذكي:** فلترة — عليه فواتير غير مدفوعة، بمنطقة معينة، إلخ
- اختار القالب المناسب
- شوف كم زبون رح يوصلهم
- أرسل وتابع النتيجة

**Acceptance Criteria:**
1. Search field with typeahead — search by name, phone, or ID
2. Selected clients shown as removable chips/list
3. Filter panel: unpaid bills count (>= N), area/district, subscription type, last payment before date
4. Combined flow: filter → manual add/remove → preview count → send
5. Result summary after sending: X sent, Y failed

---

### User Story 4 — Automation Rules (Priority: P2)

كمشرف، بدي **أشوف وأتحكم** بكل مهام الواتساب المجدولة من مكان واحد:
- قائمة بكل القواعد (تذكير شهري، إيصال دفع، إنذار قطع...)
- تشغيل/إيقاف بقائمة منسدلة
- زرار "شغّل الآن" لكل قاعدة
- آخر مرة اشتغلت ومتى الجاي

**Acceptance Criteria:**
1. Table of all scheduled WhatsApp commands with status badges
2. Toggle switch or dropdown: Active / Inactive
3. "Run Now" button executes command immediately
4. Columns: Rule name, Status, Last Run, Next Run, Frequency
5. Log of last run output (success/failure)

---

### User Story 5 — Message Log / History (Priority: P1)

كمشرف، بدي **أفتّش برسائل الواتساب** اللي انبعتت:
- دوّر بإسم الزبون أو رقمه
- فلتر بالتاريخ، النوع، الحالة (نجح/فشل)
- شوف محتوى الرسالة
- أعد إرسال الرسائل الفاشلة

**Acceptance Criteria:**
1. Paginated table: Client Name, Phone, Template, Status (✅/❌), Sent At
2. Search by client name or phone number
3. Filter by: date range, status, template type
4. Expand row to see full message content
5. "Resend" button on failed items
6. Max 30 days of history visible by default

---

### User Story 6 — Queue Status (Priority: P2)

كمشرف، بدي أشوف إذا في رسايل **عم تنتظر** بالإرسال حالياً:
- كم رسالة في الطابور
- كم عم ترسل هلأ
- كم فشلت
- إعادة إرسال الفاشل
- إيقاف مؤقت للإرسال

**Acceptance Criteria:**
1. Real-time-ish status: Pending / Sending / Failed counts
2. List of recent queue items with status
3. "Resend Failed" button
4. "Pause/Resume" toggle for queue
5. If no queue system exists, show a simplified "Last 50 messages" view instead

---

## Design / Layout

### Navigation Structure

```
📱 WhatsApp Control Center        ← Sidebar link (under Settings or as standalone)
├── 📊 Dashboard                  ← Default landing tab
├── 📝 Templates                  ← Message editor
├── 📨 Send                       ← Targeted broadcast
├── 🤖 Automation                 ← Cron rule management
├── 📋 Message Log                ← History
└── ⏳ Queue                      ← Pending status
```

### Sidebar Placement

The sidebar link replaces the current `Settings → WhatsApp` with a direct entry:
```
⚙️ الإعدادات
├── ...
└── 📱 WhatsApp Control Center    ← NEW
```

Or optionally as a standalone top-level menu item:
```
📱 WhatsApp Control Center        ← NEW (direct sidebar link)
```

### Layout Type

Tabbed interface within the Control Center page — each tab is a full section but all share the same parent URL structure:

```
/admin/whatsapp/dashboard
/admin/whatsapp/templates
/admin/whatsapp/send
/admin/whatsapp/automation
/admin/whatsapp/log
/admin/whatsapp/queue
```

---

## Technical Notes

### Database Changes

| Table | New Columns | Purpose |
|-------|-------------|---------|
| `app_config` | `whatsapp_templates` (JSON) | Store editable template bodies |
| `whatsapp_message_log` | **NEW TABLE** | Log every sent message |
| `app_config` | `whatsapp_automation_rules` (JSON) | Store rule configs (status, schedule) |

### `whatsapp_message_log` Schema (new)

| Column | Type | Purpose |
|--------|------|---------|
| `id` | BIGINT AUTO_INCREMENT | PK |
| `client_id` | INT | FK to tbl_clients |
| `client_name` | VARCHAR(255) | Denormalized for search |
| `client_phone` | VARCHAR(50) | Denormalized for search |
| `template_type` | VARCHAR(50) | reminder / receipt / etc |
| `message_body` | TEXT | Full message sent |
| `status` | ENUM('sent','failed','pending') | Delivery status |
| `error_message` | TEXT | Error details if failed |
| `sent_by` | VARCHAR(100) | 'system' / 'admin:{id}' |
| `created_at` | TIMESTAMP | When sent |

### Existing Files to Modify

| File | Change |
|------|--------|
| `app/Http/Controllers/Admin/WhatsAppSettingsController.php` | Refactor into sections + add new methods |
| `routes/admin.php` | New routes for all tabs + API endpoints |
| `resources/views/dashbord/settings/whatsapp.blade.php` | Replace entirely with tabbed layout |
| `lang/ar/clients.php` | Add translations for new UI |
| `app/Services/WhatsApp/WhatsAppMessageBuilder.php` | Support template variables from DB |
| `app/Console/Commands/WhatsAppRemindersCommand.php` | Log to `whatsapp_message_log` |
| `app/Services/WhatsApp/PaymentReceiptNotifier.php` | Log to `whatsapp_message_log` |

### New Files to Create

| File | Purpose |
|------|---------|
| `app/Models/WhatsAppMessageLog.php` | Eloquent model |
| `database/migrations/xxxx_create_whatsapp_message_logs_table.php` | Migration |
| `resources/views/dashbord/whatsapp/` | **NEW folder** — all Control Center views |
| `resources/views/dashbord/whatsapp/dashboard.blade.php` | Dashboard tab |
| `resources/views/dashbord/whatsapp/templates.blade.php` | Templates tab |
| `resources/views/dashbord/whatsapp/send.blade.php` | Send tab |
| `resources/views/dashbord/whatsapp/automation.blade.php` | Automation tab |
| `resources/views/dashbord/whatsapp/log.blade.php` | Message Log tab |
| `resources/views/dashbord/whatsapp/queue.blade.php` | Queue tab |

---

## Phasing

| Phase | Scope | Priority |
|:-----:|-------|:--------:|
| **P1** | Dashboard + Templates + Send + Message Log | 🚀 MVP |
| **P2** | Automation Rules + Queue | 📈 Enhancement |

---

## Open Questions

1. **Sidebar placement** — Should it be under Settings or a top-level menu item?
2. **Template storage** — JSON in `app_config` or separate `whatsapp_templates` table?
3. **Log retention** — Auto-delete after 90 days? Or keep forever?
4. **Queue** — Build real queuing or use simplified "recent messages" view for now?
5. **Broadcast permissions** — Should "Send" be restricted to certain admin roles?

---

## Related Specs

- [[whatsapp-emergency-kill-switch.md]] — Emergency Stop still relevant
- [[unified-whatsapp-system.md]] — Unified template foundation
- [[whatsapp-payment-notification.md]] — Payment receipt template
- [[whatsapp-receipt-collector-time.md]] — Receipt collector/time fields
