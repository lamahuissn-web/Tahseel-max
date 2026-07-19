# /ar/admin/clients UX Improvement Plan

## Goal
Improve the Clients index page for daily desktop and mobile operations without breaking existing CRUD, DataTables pagination, filters, SAS4 status checks, quick panels, invoice links, or permissions.

## Current baseline from inspection
- Route: `GET /ar/admin/clients` → `Admin\ClientController@index`.
- View: `resources/views/dashbord/clients/index.blade.php`.
- Server-side DataTables with AJAX to same route.
- Current data: 1,527 clients, 1,170 active, 357 inactive, 15,883 invoices.
- Render baseline: ~118ms, HTML ~166KB.
- AJAX baseline: ~168ms for first 10 active clients.
- Current JS expects `json.total_remaining`, but backend response does not provide it.
- DB indexes are minimal: only primary keys observed on `tbl_clients` and `tbl_invoices`.

## Safety rules
- Work on dedicated branch `feature/clients-index-ux-phases`.
- No unrelated WhatsApp Control Center changes.
- No schema/index migrations until explicitly approved for the performance phase.
- Use phase-by-phase implementation with verification after each phase.
- Keep `.hermes/` and `public/files` untracked and out of commits unless explicitly requested.

## Phase 1 — Low-risk desktop visibility improvements
Risk: Low.
Scope:
1. Add useful summary cards above the table:
   - Total filtered clients.
   - Active / inactive context where available.
   - Total remaining amount for the current filter.
   - Average remaining amount for filtered clients.
2. Return `total_remaining` from the AJAX endpoint so existing JS summary logic works.
3. Make remaining amount more actionable:
   - Desktop: clickable remaining amount opens remaining invoices modal.
   - Mobile: keep existing remaining modal behavior.
   - Colorize zero/positive remaining amount without changing business logic.
4. Keep current filters/table/actions intact.
Acceptance:
- Page renders.
- DataTables AJAX returns `total_remaining`.
- Summary fields update from AJAX response.
- Remaining amount click target exists on desktop and mobile.
- No syntax or JS parse errors.

## Phase 2 — Better operational filters
Risk: Medium.
Scope:
1. Add balance filter: all / has remaining / no remaining.
2. Add subscription filter if low-cost to populate.
3. Preserve server-side pagination and searches.
Acceptance:
- Filters combine correctly with existing name/other/type/inactive filters.
- Balance filter supports all / has remaining / no remaining using real invoice `remaining_amount` data.
- Subscription filter is populated from active subscriptions and filters by `subscription_id`.
- User/collector filter is intentionally excluded because it is not useful on this page.
- Summary cards continue to respect the current filters.
- AJAX remains responsive under 2 seconds for default and filtered requests.

## Phase 3 — Mobile card-style layout
Risk: Medium.
Scope:
1. On mobile, display each row as a compact client card.
2. Prioritize: name, phone, remaining amount, status, SAS4, actions.
3. Use large tap targets for details, invoices, remaining, SAS4.
4. Avoid horizontal scrolling where possible.
Acceptance:
- Mobile rows are readable under 480px.
- Existing modals and actions still work.

## Phase 4 — Performance hardening
Risk: Medium/high, requires explicit approval because it may add indexes/migration.
Scope:
1. Add or propose DB indexes for fields used by filters and relations:
   - `tbl_clients(is_active)`
   - `tbl_clients(client_type)`
   - `tbl_clients(name)`
   - `tbl_clients(phone)`
   - `tbl_clients(user)`
   - `tbl_clients(sas_username)`
   - `tbl_invoices(client_id)`
   - `tbl_invoices(client_id, remaining_amount)`
2. Re-measure AJAX timings before/after.
Acceptance:
- No query regressions.
- Index changes are reversible and approved before applying.

## Verification commands
- `php -l app/Http/Controllers/Admin/ClientController.php`
- `php -l resources/views/dashbord/clients/index.blade.php`
- `php artisan view:clear && php artisan view:cache`
- Render `/ar/admin/clients` through controller with authenticated admin.
- Hit AJAX endpoint with default and filtered requests.
- Extract inline scripts and run `node --check`.
