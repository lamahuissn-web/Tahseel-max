# Plan

1. Write the RED feature test first (`ClientNextDueDateTest`) with the exact
   contract: earliest outstanding due date, paid and soft-deleted invoices
   ignored, null when nothing is outstanding, per-row isolation, and a query
   count that does not grow with the page size.
2. Add a second grouped subquery to the clients list query (`next_due_date`)
   and expose `nextDueDate` from `ClientResource`.
3. Run the focused suite plus the neighbouring client/unpaid/collector suites.
4. Independent read-only audit.
5. Commit, push, PR against the active development line.
