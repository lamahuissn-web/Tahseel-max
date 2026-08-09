# Plan

1. Reproduce the production endpoint failure for linked versus unlinked clients.
2. Trace the swallowed exception inside `ClientsController::clientInvoices`.
3. Add an endpoint regression with a paid revenue whose collector relation is missing.
4. Confirm RED (`result=false`).
5. Use null-safe relation access for `collected_by` only.
6. Run details/filter, Feature 009, auth, and secure-payment regressions.
7. Commit, push, deploy Backend only, then verify the real linked client endpoint.
