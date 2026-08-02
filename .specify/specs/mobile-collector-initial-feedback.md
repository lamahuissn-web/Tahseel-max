# Mobile Collector Initial Feedback

**Status:** Approved / In Progress

## Problem Statement

The first mobile field test revealed three issues: active customers time out on initial load, customer paid/unpaid invoices are stacked vertically, and Invoice Review does not represent the collector's actual collection ledger.

## Backend Contract

### Paginated customers

`GET /api/v1/clients?page=1&per_page=50&search=`

Preserve `data.clients`; add `data.pagination` with `current_page`, `last_page`, `per_page`, `total`, and `has_more`.

### Collector collections

`GET /api/v1/collections?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD&page=1&per_page=50`

The authenticated collector is always derived from JWT. The client cannot request another collector.

Response data:
- `collections`: one item per non-deleted `tbl_revenues` row.
- `summary`: `count`, `total_amount`, `currency`, `start_date`, `end_date`.
- `pagination`: standard page metadata.

Ordering: `received_at DESC, id DESC`.

## Safety

- Read-only endpoints only.
- No schema changes.
- No payment endpoint changes or calls.
- Real mobile collection remains blocked pending a separate guarded-payment specification.

## Files

- `routes/api.php`
- `app/Http/Controllers/Api/ClientsController.php`
- New read-only collection controller/resource if useful
- Backend regression tests

## Acceptance

- Initial client API page completes below the app timeout with current production volume.
- Collections expose only the current collector's records and use `received_at` rather than invoice dates.
- No secrets or PII appear in logs/tests.
