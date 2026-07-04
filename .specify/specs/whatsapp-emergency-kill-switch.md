# Feature Specification: WhatsApp Emergency Kill Switch 🚨

**Feature Branch**: `feature/whatsapp-emergency-kill-switch`

**Created**: 2026-07-04

**Status**: Draft

**Input**: Kira request: "نحط بل إعدادات طريقة إذا صار مشكلة نقدر نوقف إرسال رسائل واتساب. شو رايك؟"

---

## User Stories & Testing

### User Story 1 — 🛑 Emergency Kill Switch (Panic Button) (Priority: P1)

كمدير (Kira), بدي لما يظهر مشكلة مثل اللي صارت (عملية rogue PHP ترسل آلاف الرسائل), أضغط زر واحد بالإعدادات يوقف **كل** إرسال واتساب فوراً بدون ما أدخل SSH.

**Why this priority**: P1 — هذه هي الميزة الأساسية المطلوبة. الهدف منها تجنب كارثة إرسال رسائل عشوائية للعملاء.

**Independent Test**: 
1. افتح صفحة إعدادات واتساب
2. اضغط زر "🛑 إيقاف الطوارئ"
3. تأكد أن:
   - `whatsapp_enabled` صار 0 (يمنع الإرسال من Laravel)
   - `whatsapp_auto_enabled` صار 0 (يمنع الإرسال التلقائي)
   - اتصال OpenWA تم قطعه
   - أي عمليات PHP شغالة متعلقة بالواتساب انقتلت
   - زر "إيقاف الطوارئ" اختفى وظهر "الخدمة موقوفة" مع زر التشغيل

**Acceptance Scenarios**:

1. **Given** الخدمة شغالة بشكل طبيعي, **When** أضغط "🛑 إيقاف الطوارئ", **Then** كل الإرسال يتوقف فوراً وتظهر رسالة "تم إيقاف الخدمة" مع تحوّل الأزرار
2. **Given** الخدمة موقوفة (emergency mode), **When** أضغط "▶️ إعادة تشغيل الخدمة", **Then** كل شي يرجع للوضع الطبيعي
3. **Given** الخدمة موقوفة (emergency mode) وفشل إعادة تشغيل OpenWA, **When** أضغط إعادة تشغيل, **Then** يعيد تفعيل Laravel settings على الأقل ويظهر warning عن OpenWA

---

### User Story 2 — 🧹 Kill Rogue PHP Processes (Priority: P1)

بعد مشكلة الـ rogue PHP process اللي صارت (test_send_monthly.php)، لازم الـ Kill Switch يقدر يقتل أي عمليات PHP شغالة بالخلفية يمكن تكون مرسلة رسايل.

**Why this priority**: P1 — نفس المشكلة صارت حرفياً، وأهم شي إنو زر الطوارئ يحل المشكلة مهما كان مصدرها.

**Independent Test**: 
1. شغّل `php /tmp/test_send.php &` في الخلفية (محاكاة rogue process)
2. افتح صفحة الإعدادات واضغط "🛑 إيقاف الطوارئ"
3. تأكد أن العملية انقتلت

**Acceptance Scenarios**:

1. **Given** في عملية PHP شغالة بالخلفية (اسمها يحتوي `test_send`, `whatsapp`, `reminder`, `monthly`), **When** نضغط Kill Switch, **Then** العملية تنقتل
2. **Given** ما في أي عمليات PHP شغالة, **When** نضغط Kill Switch, **Then** ما يظهر error والـ kill يكون silent

---

### User Story 3 — 📋 سجل أحداث الطوارئ (Priority: P2)

سجل (Log) لكل مرة تم فيها استخدام Kill Switch أو إعادة التشغيل: الوقت، المسبب (من الموقع), والنظام المستهدف.

**Why this priority**: P2 — مفيد للتدقيق لاحقاً ولكن مش ضروري للحماية الفورية.

**Acceptance Scenarios**:

1. **Given** ضغطت Kill Switch, **When** يكتب log, **Then** أقدر أشوف سجل الأحداث بصفحة الإعدادات
2. **Given** في أكثر من حدث, **When** أشوف السجل, **Then** الأحداث مرتبة من الأحدث للأقدم

---

### Edge Cases

- **الخدمة موقوفة أصلاً**: إذا ضغط Kill Switch والخدمة موقوفة، يشتغل عادي (يقفل أبواب زيادة)
- **OpenWA disconnected أصلاً**: ما يتوقف عند قطع OpenWA، يكمل باقي الخطوات
- **ما في PHP processes**: ما يظهر error
- **فشل supervisor stop**: يسجل warning بس يكمل باقي الإجراءات
- **تم الضغط مرتين متتاليتين**: ما يعمل شي في المرة الثانية (الخدمة موقوفة أصلاً)

---

## Requirements

### Functional Requirements

- **FR-001**: System MUST provide a visible "🛑 إيقاف الطوارئ" button on WhatsApp settings page
- **FR-002**: When activated, system MUST set `whatsapp_enabled = 0` immediately in `app_config`
- **FR-003**: When activated, system MUST set `whatsapp_auto_enabled = 0` immediately in `app_config`
- **FR-004**: When activated, system MUST disconnect OpenWA session (POST `/sessions/{id}/stop`)
- **FR-005**: When activated, system MUST kill any rogue PHP processes matching `whatsapp`, `reminder`, `monthly`, `test_send`
- **FR-006**: When activated, system MUST clear Laravel cache
- **FR-007**: System MUST provide a "▶️ إعادة تشغيل الخدمة" button to revert all actions
- **FR-008**: When re-enabling, system MUST set `whatsapp_enabled` back to previous value (or 1)
- **FR-009**: When re-enabling, system MUST start OpenWA session (POST `/sessions/{id}/start`)
- **FR-010**: System MUST show current emergency status in the UI (خدمة شغالة / موقوفة طوارئ)
- **FR-011**: System MUST NOT stop the Supervisor Node.js service — only disconnect the session (to avoid breaking the service permanently)
- **FR-012**: Emergency mode state MUST be stored in `app_config` table as `whatsapp_emergency_stop`

### Key Entities

- **app_config**: Key-value table — stores `whatsapp_enabled`, `whatsapp_auto_enabled`, `whatsapp_emergency_stop`
- **WhatsAppSettingsController**: Existing — add `emergencyStop()` and `emergencyRestart()` methods
- **WhatsAppService**: Existing OpenWA API client — add `disconnectSession()` and `connectSession()` if not exist
- **Supervisor**: `whatsapp-service` — NOT stopped, only OpenWA session disconnected

---

## Success Criteria

- **SC-001**: Kill Switch stops ALL outgoing WhatsApp within 3 seconds of clicking
- **SC-002**: Restart button restores service in under 10 seconds
- **SC-003**: Rogue PHP process killed reliably
- **SC-004**: No SSH access needed for emergency stop or restart
- **SC-005**: Currently running messages in flight may still deliver (can't recall sent messages)
- **SC-006**: No new messages sent after emergency stop activates

---

## UI Design

### الموقع في صفحة الإعدادات

```
┌─────────────────────────────────────────────┐
│  🌐 اتصال واتساب                            │
│  🟢 متصل | 14244609209                      │
│                                             │
│  [🔄 إعادة تشغيل الخدمة]  [🛑 إيقاف الطوارئ] │  ← خط الطوارئ الجديد
└─────────────────────────────────────────────┘
```

### عند تفعيل الطوارئ

```
┌─────────────────────────────────────────────┐
│  🌐 اتصال واتساب                            │
│  🔴 موقوف بواسطة الطوارئ 🚨                  │
│  ⏱️ تم الإيقاف: 04-07-2026 21:30            │
│                                             │
│  [▶️ إعادة تشغيل الخدمة]                    │
└─────────────────────────────────────────────┘
```

### Confirm Dialog

```
┌──────────────────────────────┐
│  🚨 تأكيد إيقاف الطوارئ       │
│                              │
│  هل أنت متأكد؟ هذا سيوقف     │
│  جميع رسائل الواتساب فوراً   │
│  ويقطع اتصال OpenWA.         │
│                              │
│  [🛑 نعم، أوقف الخدمة] [إلغاء]│
└──────────────────────────────┘
```

---

## What We WON'T Do (Out of Scope)

- ❌ إيقاف Supervisor service نفسه (نخلي Node service شغال عشان ما نكسره)
- ❌ إرسال إشعار للمسؤول عند الطوارئ (لاحقاً)
- ❌ Auto-recovery (رجوع تلقائي)
- ❌ Kill Switch للـ Telegram bot (feature منفصلة)
- ❌ سجل أحداث مفصل (P2 — ممكن لاحقاً)

---

## Assumptions

- OpenWA API available at `OPENWA_API_URL` + `/sessions/{sessionId}/stop` and `/sessions/{sessionId}/start`
- Rogue PHP processes identifiable by their command lines (grep `php` + `whatsapp|reminder|monthly|test_send`)
- Emergency state stored in `app_config` table
- Supervisor `whatsapp-service` keeps running — only OpenWA session toggled
- UI uses Bootstrap 5 + existing Blade structure with RTL
