# Spec 020: Outstanding Due Date on the Mobile Subscriber List

## Problem
The collector-facing subscriber list (`GET /api/v1/clients`) gave no usable due
date, so a collector had to open every customer to learn who is due.

The payload did carry `subscriptionDate`, but it is the LATEST invoice due date
(including fully paid invoices), produced by the `Clients::getLatestInvoiceDueDateAttribute`
accessor — one query per client row (N+1).

## Requirement
- Add `nextDueDate`: the EARLIEST `due_date` among the client's outstanding
  (unpaid/partial, non-soft-deleted) invoices.
- Null — never `''` and never `N/A` — when the client has nothing outstanding,
  so the app can render «لا يوجد استحقاق».
- Resolve it with ONE grouped subquery per page; never a query per client row.
- Leave `subscriptionDate` behaviour untouched.

## Non-goals
- No payment, WhatsApp, SAS, or queue change.
- No change to the existing per-row `subscriptionDate` accessor (tracked debt).
- No removal of the one-year window in the client-invoices endpoint.

## Evidence
Data check on the development database: 1,196 active clients have at least one
outstanding invoice; the earliest outstanding due dates run back to 2025.
Indexes in place: `idx_tbl_invoices_status_due (status, deleted_at, due_date)`
and `idx_tbl_invoices_client_id`.

## Safety
Read-only aggregation added to an existing list query. No financial state,
payment submission, idempotency, or reconciliation logic touched.
