# الملفات التي تم تعديلها اليوم

## ملخص التغييرات

### 1. إضافة Sorting للفواتير (تاريخ الاستحقاق وتاريخ الدفع)
### 2. إضافة زر تصدير جميع البيانات إلى Excel في header الداشبورد
### 3. إنشاء Export Class لتصدير جميع البيانات في ملف Excel متعدد الأوراق

---

## الملفات المعدلة:

### 1. `resources/views/dashbord/invoices/index.blade.php`
**التغييرات:**
- إضافة حقل "الترتيب حسب" مع خيارات (رقم الفاتورة، تاريخ الاستحقاق، تاريخ الدفع)
- إضافة حقل "نوع الترتيب" (تصاعدي/تنازلي)
- تحديث JavaScript لإرسال `sort_by` و `sort_order` في AJAX request
- تحديث زر Reset لإعادة تعيين قيم Sorting

**السطور المعدلة:**
- السطور 185-206: إضافة حقول Sorting
- السطر 343: إضافة `d.sort_by`
- السطر 512: إعادة تعيين `sort_by` في Reset

---

### 2. `app/Http/Controllers/Admin/InvoiceController.php`
**التغييرات:**
- إضافة منطق Sorting حسب `due_date` أو `paid_date`
- إضافة method `exportAllData()` لتصدير جميع البيانات
- إضافة imports للـ Excel

**السطور المعدلة:**
- السطور 24-25: إضافة imports للـ Excel
- السطور 304-321: إضافة منطق Sorting
- السطور 1149-1153: إضافة method `exportAllData()`

---

### 3. `routes/admin.php`
**التغييرات:**
- إضافة route جديد للتصدير

**السطور المعدلة:**
- السطر 126: إضافة route `admin.invoices.export_all_data`

---

### 4. `resources/views/dashbord/layouts/main-headerbar.blade.php`
**التغييرات:**
- إضافة زر "تصدير جميع البيانات" في header الداشبورد

**السطور المعدلة:**
- السطور 55-64: إضافة زر التصدير

---

## الملفات الجديدة:

### 5. `app/Exports/AllDataExport.php` (ملف جديد)
**الوصف:**
- Export Class يحتوي على 9 أوراق منفصلة:
  1. العملاء (ClientsSheet)
  2. الفواتير (InvoicesSheet)
  3. المستخدمين (UsersSheet)
  4. الموظفين (EmployeesSheet)
  5. المصروفات (ExpensesSheet)
  6. الإيرادات (RevenuesSheet)
  7. التحويلات (TransfersSheet)
  8. الحركات المالية (FinancialTransactionsSheet)
  9. الدليل المحاسبي (AccountsSheet) - مع حساب المبلغ الإجمالي لكل حساب

**الميزات:**
- كل ورقة تحتوي على Headings بالعربية
- حساب المبلغ الإجمالي للحسابات في الدليل المحاسبي
- استخدام الأعمدة الصحيحة من قاعدة البيانات

---

## ملاحظات:
- تم إصلاح أخطاء SQL (whatsapp_num في العملاء، amount في المصروفات)
- تم نقل زر التصدير من صفحة الفواتير إلى header الداشبورد
- جميع التغييرات متوافقة مع بنية قاعدة البيانات الحالية

