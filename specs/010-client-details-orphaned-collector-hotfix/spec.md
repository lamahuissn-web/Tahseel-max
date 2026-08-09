# Feature 010 — Client Details Orphaned Collector Hotfix

## Problem
`GET /api/v1/clients/{id}/invoices` crashes when a historical `tbl_revenues.collected_by` references an admin that no longer exists. The controller dereferences `$revenue->user->name` while the relation is null, so Flutter receives `result=false` and cannot render client details.

The affected production client happens to have `sas_username`, but SAS is not the cause.

## Acceptance Criteria
- Client details succeeds when a historical revenue has no collector relation.
- The affected row returns `collected_by: null`.
- Normal collector names remain unchanged.
- No database mutation, migration, payment behavior, SAS behavior, or Flutter change.
- JWT and existing invoice scoping remain unchanged.
