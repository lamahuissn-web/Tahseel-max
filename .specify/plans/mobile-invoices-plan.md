# Implementation Plan: Mobile-Responsive Invoices View

**Branch**: `feature/mobile-invoices` | **Date**: 2026-06-28 | **Spec**: `.specify/specs/mobile-invoices.md`

## Summary

تحويل عرض الفواتير في صفحة العميل من جدول (DataTable) إلى بطاقات (cards) على الشاشات الصغيرة (<768px)، مع إبقاء الجدول على الشاشات الكبيرة. بدون أي تغيير في Controller أو Database — فقط CSS + طبقات عرض (Views).

## Technical Context

**Language/Version**: PHP 8.3, Laravel 10, Blade, Bootstrap 5

**Primary Dependencies**: jQuery DataTables (موجود), Bootstrap Icons (موجود)

**Storage**: N/A — لا تغيير في البيانات

**Testing**: اختبار يدوي عبر Chrome DevTools في وضع الموبايل (360px-768px)

**Target Platform**: متصفحات الويب (Chrome, Firefox, Safari على الموبايل)

**Project Type**: Web application — تحسين Frontend فقط

**Performance Goals**: نفس أداء DataTables الحالي، بدون إضافة HTTP requests جديدة

**Constraints**: لا تغيير في ملفات PHP — فقط views + CSS

**Scale/Scope**: صفحة فواتير العميل الواحد (client invoices)

## Constitution Check

✅ Financial Core untouched — لا تغيير في Controllers, Models, DB
✅ Arabic-First — كل النصوص يضلّها بالعربي
✅ Keep Existing Patterns — Bootstrap 5 + jQuery كما هما
✅ Mobile Responsiveness — هذا هدف الـ feature نفسه

## Project Structure

### Files to modify

```text
resources/
└── views/
    └── dashbord/
        └── clients/
            ├── client_details.blade.php          # تعديل طفيف — إضافة CSS
            └── partials/
                └── _invoices_tab.blade.php       # ★ التعديل الأساسي هنا

public/
└── css/
    └── custom.css                                # إضافة CSS للـ mobile cards (أو في style block)
```

### Files NOT to touch
```text
app/Http/Controllers/Admin/ClientController.php     # ❌ لا تغيير
app/Models/                                         # ❌ لا تغيير
routes/                                             # ❌ لا تغيير
database/                                           # ❌ لا تغيير
```

## Implementation Strategy

بدلاً من تعديل `client_details.blade.php` (اللي هو كبير ومركب)، سنستخدم **CSS Media Queries + Bootstrap Responsive Utilities** على `_invoices_tab.blade.php` نفسه.

**الطريقة:**
1. الجدول (DataTable) يضل موجود
2. نضيف CSS يحوّل rows الـ DataTable إلى cards على الشاشات الصغيرة
3. كل `<tr>` يصير `<div>`-like card باستخدام `display: block;` على mobile
4. نضيف أزرار الدفع والطباعة داخل كل بطاقة (موجودة حالياً كأعمدة)

هالطريقة:
- ✅ ما تكسر DataTable ولا pagination/Search
- ✅ نفس البيانات — نفس الـ backend
- ✅ أقل تغيير — أسرع تطبيق
