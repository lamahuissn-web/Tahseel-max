# WhatsApp Automation Page — Redesign

## Status: Approved ✅

## Goal
إعادة هيكلة صفحة WhatsApp Automation لتصبح Hub واحد لإدارة الزبائن وإرسال التذكيرات وجدولة المهام، بثلاث تبويبات واضحة.

## Pain Points
1. تبويب "القواعد" الحالي abstract وغير مفهوم (قاعدتين بس بدون تفاصيل)
2. الإرسال مبعثر بين صفحة Send والتقويم و Automation
3. المستخدم يحتاج يشوف الزبائن أولاً ثم يقرر الإجراء

## Solution: 3-Tab Layout

### Tab 1: 👥 الزبائن (Customers)
فلترة الزبائن ← اختيار ← إرسال تذكير أو جدولة مهمة متكررة

### Tab 2: 📅 التقويم الشهري (Calendar)
موجود حالياً — يبقى كما هو

### Tab 3: ⏰ المهام المجدولة (Scheduled Tasks)
عرض وإدارة المهام المجدولة كبطاقات واضحة مع إحصائيات

## API Endpoints to Add
| Method | Path | Purpose |
|--------|------|---------|
| GET | /automation/clients | Filtered client list with invoice summary |
| POST | /automation/send-now | Send immediately to selected clients |
| POST | /automation/schedule-task | Create a scheduled task |
| DELETE | /automation/tasks/{id} | Delete a scheduled task |
| GET | /automation/tasks | Get tasks with stats from logs |

## Files to Modify
- resources/views/dashbord/whatsapp/automation.blade.php — Full restructure
- app/Http/Controllers/Admin/WhatsAppControlCenterController.php — Add endpoints
- routes/admin.php — Add routes
