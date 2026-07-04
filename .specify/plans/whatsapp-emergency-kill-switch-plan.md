# Implementation Plan: WhatsApp Emergency Kill Switch 🚨

**Branch**: `feature/whatsapp-emergency-kill-switch` | **Date**: 2026-07-04 | **Spec**: `.specify/specs/whatsapp-emergency-kill-switch.md`

## Summary

إضافة **زر الطوارئ (Kill Switch)** في صفحة إعدادات واتساب. ضغطة واحدة توقف كل إرسال رسائل واتساب فوراً: تعطيل `whatsapp_enabled` و `whatsapp_auto_enabled`, قطع اتصال OpenWA, قتل أي عمليات PHP rogue, ومسح الكاش. مع زر إعادة تشغيل يرجع الخدمة.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 10, Blade, Bootstrap 5

**Primary Dependencies**: jQuery (موجود), Bootstrap Icons (موجود), Supervisor (whatsapp-service)

**Storage**: `app_config` table — key-value storage للإعدادات

**Testing**: اختبار يدوي عبر صفحة الإعدادات — ضغط زر الطوارئ ثم إعادة التشغيل

**Target Platform**: متصفحات الويب (Laravel admin panel)

**Project Type**: Web application — إضافة على صفحة الإعدادات الحالية

**Performance Goals**: استجابة kill switch خلال 3 ثواني

**Constraints**: 
- ما نوقف Supervisor service نفسه (فقط OpenWA session)
- ما نلمس الفواتير أو الدفعات أو الـ financial core
- كل الإجراءات من السيرفر نفسه (CT 131) عبر SSH داخلي

## Constitution Check

✅ Financial Core untouched — لا تغيير في Controllers المالية أو Models
✅ Arabic-First — كل النصوص بالعربي
✅ Keep Existing Patterns — Bootstrap 5 + jQuery + Laravel Controller patterns
✅ Mobile Responsiveness — الزر يظهر عادي عالموبايل

## Project Structure

### Files to modify

```text
resources/views/dashbord/settings/whatsapp.blade.php   # ★ إضافة الزر + UI
app/Http/Controllers/Admin/WhatsAppSettingsController.php  # ★ 2 دوال جديدتين
routes/admin.php                                          # route جديدين
```

### Files to create

```text
lang/ar/clients.php                    # إضافة translation keys (تعديل موجود)
```

### Implementation

### 1. Controller Methods (`WhatsAppSettingsController.php`)

#### `emergencyStop()` method:
```
1. Set whatsapp_enabled = 0 in app_config
2. Set whatsapp_auto_enabled = 0 in app_config
3. Set whatsapp_emergency_stop = 1 + timestamp in app_config
4. Disconnect OpenWA session (POST /sessions/{sessionId}/stop)
5. Kill PHP rogue processes:
   - exec("pkill -f 'php.*(whatsapp|reminder|monthly|test_send)'")
6. Clear Laravel cache
7. Return JSON response { success: true, message: "..." }
```

#### `emergencyRestart()` method:
```
1. Set whatsapp_enabled = 1 (or restore previous value)
2. Set whatsapp_emergency_stop = 0 in app_config
3. Start OpenWA session (POST /sessions/{sessionId}/start)
4. Clear Laravel cache
5. Return JSON response { success: true, message: "..." }
```

### 2. Routes (`routes/admin.php`)

```php
Route::post('settings/whatsapp/emergency-stop', ...)->name('settings.whatsapp.emergency_stop');
Route::post('settings/whatsapp/emergency-restart', ...)->name('settings.whatsapp.emergency_restart');
```

### 3. UI (`whatsapp.blade.php`)

- جنب زر "إعادة تشغيل الخدمة" الحالي، نضيف زر "🛑 إيقاف الطوارئ" (أحمر)
- إذا `whatsapp_emergency_stop = 1`:
  - نظهر رسالة "🔴 موقوف بواسطة الطوارئ" بدل حالة الاتصال
  - نضهر وقت الإيقاف
  - نخفي زر الطوارئ ونظهر "▶️ إعادة تشغيل الخدمة" (أخضر)
- Confirm dialog قبل الطوارئ

### 4. Translation Keys (`lang/ar/clients.php`)

- `whatsapp_emergency_stop` = "🛑 إيقاف الطوارئ"
- `whatsapp_emergency_restart` = "▶️ إعادة تشغيل الخدمة"
- `whatsapp_emergency_stopped` = "🔴 موقوف بواسطة الطوارئ 🚨"
- `whatsapp_emergency_confirm` = "هل أنت متأكد؟ هذا سيوقف جميع رسائل الواتساب فوراً ويقطع اتصال OpenWA."
- `whatsapp_emergency_stopped_at` = "⏱️ تم الإيقاف:"
- `whatsapp_emergency_restarting` = "جاري إعادة التشغيل..."
- `whatsapp_emergency_stopped_msg` = "✅ تم إيقاف الخدمة بنجاح"
- `whatsapp_emergency_restarted_msg` = "✅ تم إعادة تشغيل الخدمة بنجاح"
