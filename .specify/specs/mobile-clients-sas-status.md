# Mobile Clients SAS Status

## Goal
Collectors using `/ar/admin/mobile-clients` need to identify a customer's linked SAS username and see whether that SAS account is online, offline, disabled, or unknown.

## Scope
1. Mobile clients search supports client name, phone, and `sas_username`.
2. Mobile client cards show the stored `sas_username` clearly.
3. Cards show a read-only SAS status badge.
4. Status is loaded asynchronously using the existing `admin.sas4.online_status` endpoint for visible cards only.
5. The UI must not expose SAS control actions to collectors.

## Acceptance
- Searching by a known `sas_username` returns that client.
- Each card with `sas_username` renders a `mobile-sas-indicator` with `data-username`.
- Cards without SAS show a neutral `غير مربوط` state and are not sent to the online-status endpoint.
- Status badge updates to `متصل`, `غير متصل`, `موقوف`, or `غير معروف` without blocking initial search results.
- Existing mobile infinite scroll still works.
- Desktop clients page behavior remains unchanged.
