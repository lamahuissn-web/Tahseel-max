# Feature Specification: Filter Result Review Modal for Send Page

**Feature Branch**: `feature/whatsapp-control-center`

**Created**: 2026-07-12 | **Updated**: 2026-07-12

**Status**: Draft

**Input**: Kira's feedback — after applying smart filters, adding all matching customers at once removes user control. The user wants to review and select which filtered customers to add.

---

## Problem Statement

Currently, when a user applies smart filters on the send page:
1. Filters are set in a modal (`#filterModal`)
2. Click "تطبيق وإضافة"
3. All matching customers are **automatically** added as chips to the "إلى:" field
4. A success toast shows the count

**Problems**:
| Issue | Impact |
|-------|--------|
| 😵 **No visibility** | User can't see WHO matched the filter before adding |
| 🚫 **No selection control** | Can't exclude specific customers from the filter results |
| ❌ **Accidental sends** | Filter might match wrong customers with no review step |
| 📋 **No double-check** | User must trust the filter blindly |

**Goal**: Add a **review modal** between filter application and customer selection, showing matching customers in a table with individual checkboxes, select-all, and a confirmed "إضافة المحددين" action.

---

## User Stories

### User Story 1 — Review Filter Results

كمشرف، بعد ما أضبط الفلاتر الذكية بدي أشوف مين الزباين اللي طابقوا هالفلاتر قبل ما أضيفهم. يظهرلي **جدول** بقائمة الزبائن مع معلوماتهم (الاسم، الرقم، الحالة، عدد الفواتير)، وأقدر أحدد مين بدي أضيف بالضبط.

### User Story 2 — Select/Deselect from Results

بدي أختار الزباين اللي بدي أرسلهم يدويًا من النتائج، مش كل اللي طابق الفلتر. في خيار **تحديد الكل** و **إلغاء تحديد الكل**، وكل زبون له checkbox لحاله.

### User Story 3 — Confirm Addition

بعد ما أختار، بدي أضغط "إضافة المحددين" عشان يضافوا كـ chips بحقل "إلى:"، وأكمل عملية الإرسال بشكل طبيعي.

---

## Design

### New Modal: Filter Results Review

```
┌──────────────────────────────────────────────────┐
│  📋 نتائج الفلتر — ٢٣ زبون                       │
│  ─────────────────────────────────────           │
│                                                  │
│  [☐ تحديد الكل]                                  │
│                                                  │
│  ┌────┬────────────────┬──────────┬──────┬───┐   │
│  │ #  │ الاسم           │ الرقم    │الحالة│عدد│   │
│  ├────┼────────────────┼──────────┼──────┼───┤   │
│  │ ☑  │ أحمد محمد      │70123456  │🟢   │ ٢ │   │
│  │ ☑  │ سارة علي       │70345678  │🔴   │ ١ │   │
│  │ ☐  │ خالد حسن       │70567890  │🟢   │ ٥ │   │
│  │ ...│                │          │      │   │   │
│  └────┴────────────────┴──────────┴──────┴───┘   │
│                                                  │
│  [⬅️ العودة للفلاتر]          [➕ إضافة المحددين]  │
│                                   (١٢ زبون)       │
└──────────────────────────────────────────────────┘
```

### Behavior

1. User clicks "تطبيق وإضافة" in filter modal
2. Loading state → fetch filtered clients
3. **New modal opens** showing results in a table
   - Each row has a checkbox
   - Header has "تحديد الكل" checkbox
   - Shows: name, phone, status (active/inactive), unpaid count
4. User checks/unchecks rows
5. Click "إضافة المحددين (N)" → close modal → add selected clients as chips
6. Click "العودة للفلاتر" → close results modal → re-open filter modal

### Changes Required

**Backend (Controller)**:
- The `broadcast()` preview endpoint already returns the filtered clients correctly — no change needed

**Frontend (send.blade.js)**:
- Remove the direct `addClient()` loop from the filter success handler
- Replace with opening a new modal (`#filterResultsModal`)
- New modal HTML with table and checkboxes
- JS logic for: select-all, individual selection, update count, add selected

---

## Acceptance Criteria

1. ✅ After applying filters, a modal appears with a list of matching customers
2. ✅ Each customer row has a checkbox
3. ✅ "تحديد الكل" checkbox works (check all / uncheck all)
4. ✅ Count updates dynamically based on checked rows
5. ✅ "إضافة المحددين" adds only checked customers as chips to "إلى:" field
6. ✅ "العودة للفلاتر" returns to filter modal without adding anyone
7. ✅ If no customers match the filter, show "لا يوجد زبائن متطابقين" (current behavior preserved)
8. ✅ The rest of the send page (template, preview, send) remains unchanged

---

## Files Changed

| File | Change |
|------|--------|
| `resources/views/dashbord/whatsapp/send.blade.php` | Add `#filterResultsModal` HTML, update JS filter handler |
| `public/assets/js/whatsapp-send.js` (or inline `<script>`) | New JS logic for results modal |

(No controller changes needed — existing `broadcast()` preview endpoint is sufficient.)

---

## Risks

- Large result sets (>200): current backend limit is 200. Could add "إظهار الكل" but out of scope for now.
- Client names that overflow table: handled by responsive table with text truncation or wrap.
