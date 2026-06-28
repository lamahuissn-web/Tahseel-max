# Tasks: Mobile-Responsive Invoices View

**Branch**: `feature/mobile-invoices` | **Date**: 2026-06-28 | **Plan**: `.specify/plans/mobile-invoices-plan.md`

## Task 1: Add data-label attributes to DataTable columns

**File**: `resources/views/dashbord/clients/invoices/invoices_data.blade.php`

Add `createdRow` callback to the DataTable initialization to inject `data-label` attributes on each `<td>` based on column index. This labels the data for mobile card view.

## Task 2: Add mobile-responsive CSS

**File**: `resources/views/dashbord/clients/invoices/invoices_data.blade.php`

Add `<style>` block with media queries that:
- Hide `<thead>` on screens <768px
- Convert `<tr>` to block-level cards with border, padding, margin, shadow
- Each `<td>` shows its label from `data-label` via CSS `::before`
- Style status badges, amounts, and action buttons for mobile
- Ensure the filter row (type, status, month, dates) stacks vertically on mobile

## Task 3: Add responsive filter row

**File**: `resources/views/dashbord/clients/invoices/invoices_data.blade.php`

Add Bootstrap responsive classes to the filter row so the 6 filter fields stack in 2 columns on mobile instead of 3-4 columns.

## Task 4: Verify

Test via Chrome DevTools mobile view (375px, 414px, 768px) — filters work, cards show correctly, pagination works, desktop view unchanged.
