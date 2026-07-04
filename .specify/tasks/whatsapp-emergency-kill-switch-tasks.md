# Tasks: WhatsApp Emergency Kill Switch 🚨

**Input**: `.specify/specs/whatsapp-emergency-kill-switch.md`
**Plan**: `.specify/plans/whatsapp-emergency-kill-switch-plan.md`

---

## Phase 1: Backend — Controller Methods

**Purpose**: Add `emergencyStop()` and `emergencyRestart()` methods to WhatsAppSettingsController + routes

- [ ] T001 Add `emergencyStop()` method to `WhatsAppSettingsController.php`:
  - تعطيل `whatsapp_enabled = 0` في `app_config`
  - تعطيل `whatsapp_auto_enabled = 0`
  - تخزين `whatsapp_emergency_stop = 1` + الوقت
  - قطع OpenWA session (POST `/sessions/{id}/stop`)
  - قتل العمليات الـ rogue: `pkill -f 'php.*(whatsapp|reminder|monthly|test_send)'`
  - مسح Laravel cache
  - إرجاع JSON response

- [ ] T002 Add `emergencyRestart()` method to `WhatsAppSettingsController.php`:
  - إعادة `whatsapp_enabled = 1` في `app_config`
  - تعطيل `whatsapp_emergency_stop = 0`
  - تشغيل OpenWA session (POST `/sessions/{id}/start`)
  - مسح Laravel cache
  - إرجاع JSON response

- [ ] T003 [P] Add routes to `routes/admin.php`:
  - `POST settings/whatsapp/emergency-stop`
  - `POST settings/whatsapp/emergency-restart`

**Checkpoint**: ✅ دوال الـ Controller + Routes جاهزة

---

## Phase 2: UI — View + Translations

**Purpose**: إضافة الأزرار والـ UI في صفحة الإعدادات والنصوص

- [ ] T004 Add translation keys to `lang/ar/clients.php`:
  - `whatsapp_emergency_stop`, `whatsapp_emergency_restart`
  - `whatsapp_emergency_stopped`, `whatsapp_emergency_confirm`
  - `whatsapp_emergency_stopped_at`
  - `whatsapp_emergency_stopped_msg`, `whatsapp_emergency_restarted_msg`

- [ ] T005 Update `whatsapp.blade.php`:
  - إضافة زر "🛑 إيقاف الطوارئ" أحمر جنب زر "إعادة تشغيل الخدمة"
  - إذا الخدمة موقوفة طوارئ:
    - إظهار "🔴 موقوف بواسطة الطوارئ 🚨" + وقت الإيقاف
    - إخفاء زر الطوارئ وإظهار "▶️ إعادة تشغيل الخدمة"
  - إضافة Confirm modal
  - إضافة JavaScript دوال: `emergencyStop()`, `emergencyRestart()`
  - تحويل حالة الاتصال من `$status` لتعكس الـ emergency state

**Checkpoint**: ✅ UI جاهز مع كل الأزرار

---

## Phase 3: Verify

**Purpose**: فحص نحوي واختبار يدوي

- [ ] T006 Run `php -l` على جميع الملفات المعدّلة
- [ ] T007 فتح صفحة الإعدادات والتأكد من ظهور الأزرار
- [ ] T008 تجربة ضغط "🛑 إيقاف الطوارئ":
  - التأكد من ظهور الـ confirm dialog
  - التأكد من تحوّل واجهة المستخدم
  - التأكد من عدم إمكانية إرسال رسايل
- [ ] T009 تجربة ضغط "▶️ إعادة تشغيل الخدمة":
  - التأكد من رجوع الواجهة للوضع الطبيعي
  - التأكد من عودة اتصال OpenWA
- [ ] T010 اختبار طوارئ + rogue PHP process (محاكاة)

---

## Execution Order

```
T001 ─→ T003 ─→ T004 ─→ T005 ─→ T006 ─→ T007 ─→ T008 ─→ T009 ─→ T010
    ↕
   T002 (parallel with T001 — depends on both for tests)
```

**Commit message**: `feat: add WhatsApp emergency kill switch with panic button in settings`
