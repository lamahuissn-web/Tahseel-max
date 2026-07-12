# Tasks: Filter Result Review Modal

**Feature**: Filter Result Review Modal
**Plan**: `.specify/plans/whatsapp-send-filter-review-plan.md`
**Created**: 2026-07-12

---

## Task List

### T1 — Add Review Modal HTML to send.blade.php
**Priority**: P1 | **Status**: Pending | **Deps**: None

- [ ] Add `#filterResultsModal` with:
  - Header: "نتائج الفلتر — N زبون" with dynamic count
  - Table in body with columns: checkbox, name, phone, status (badge), unpaid count
  - Responsive design (card view on mobile)
  - Footer: "العودة للفلاتر" (btn-light) and "إضافة المحددين (N)" (btn-primary, dynamic count)
- [ ] Style the modal with proper Keen design (matching existing modals)

**Files**:
- `resources/views/dashbord/whatsapp/send.blade.php`

---

### T2 — Update Filter Apply Handler in JS
**Priority**: P1 | **Status**: Pending | **Deps**: T1

- [ ] Change `$('#applyFilters').on('click'...)` success handler:
  - Store `res.clients` in `filterResults` variable
  - Call `renderFilterResults(filterResults)` function
  - Show `#filterResultsModal` modal
  - Do NOT auto-add clients or show success toast

**Files**:
- `resources/views/dashbord/whatsapp/send.blade.php` (inline JS)

---

### T3 — Implement Review Modal Logic
**Priority**: P1 | **Status**: Pending | **Deps**: T2

- [ ] `renderFilterResults(results)` function:
  - Update modal title count
  - Build table rows HTML with checkboxes
  - Handle empty results → show message and disable add button
  - Inject into table body
- [ ] Select-all checkbox toggle:
  - Check/uncheck all individual rows
  - Update "إضافة المحددين" button count
- [ ] Individual checkbox handler:
  - Update select-all checkbox state (checked if all, unchecked if not all)
  - Update button count
- [ ] "إضافة المحددين" button handler:
  - Iterate `.filter-result-row:checked`
  - Call `addClient(id, name, phone)` for each
  - Close results modal
  - Show success toast with count
- [ ] "العودة للفلاتر" button handler:
  - Close results modal
  - Show filter modal again (preserving filter values)

**Files**:
- `resources/views/dashbord/whatsapp/send.blade.php` (inline JS)

---

### T4 — Verify & Test
**Priority**: P1 | **Status**: Pending | **Deps**: T3

- [ ] Test: Apply filter with no matching clients → shows "لا يوجد زبائن متطابقين" (current behavior preserved)
- [ ] Test: Apply filter with matching clients → modal opens with correct count
- [ ] Test: Select all → all rows checked → button shows full count
- [ ] Test: Deselect all → button disabled with "اختر مستلمين"
- [ ] Test: Select individual rows → button count updates
- [ ] Test: Click "إضافة المحددين" → chips added to "إلى:" field → modal closes
- [ ] Test: Click "العودة للفلاتر" → filter modal reopens with same values
- [ ] Test: Send with selected chips works normally
- [ ] Test: Mobile view – table collapses to card layout

---

## Rollback Plan

If the feature breaks the send page:
1. Revert the send.blade.php changes: `git checkout -- resources/views/dashbord/whatsapp/send.blade.php`
2. Re-apply only if needed: `git stash pop` or re-deploy from commit
