# Implementation Plan

## Data flow
1. Normalize `section`, message status, source, date, and numeric batch filters in the queue controller.
2. Build one reusable filtered, unarchived batch query with operational section predicates and message aggregates.
3. Compute lightweight section counts, then paginate only the selected section at 10 rows using `batches_page`.
4. Resolve a selected batch through the same safety/filter scope. Only then create a selected-batch message query and paginate 10 rows using `messages_page`; otherwise expose nullable details.
5. Keep legacy aggregate compatibility bounded to 10 groups and collapsed in the UI.

## UI
- Replace tall batch cards with RTL compact list-group rows and one progress/count line.
- Add section chips/tabs with counts and a deterministic range label.
- Render selected messages as desktop table plus mobile cards from one paginator.
- Mask phones and place technical provenance inside collapsed `<details>`.
- Preserve filters and independent paginator state in generated links.

## Tests
- SQLite controller behavior with realistic batch/message schemas.
- Blade contract/render test using the real controller data.
- RED first, then minimal controller and Blade changes.

## Verification
- Focused Feature 017 test.
- Existing WhatsApp feature suite.
- `php -l`, `php artisan view:clear`, `php artisan view:cache`.
- Render HTML, extract inline scripts, `node --check`.
- `git diff --check`, status, and scoped diff review.

## Rollback
Revert only the controller queue read path, queue Blade, test, and Feature 017 spec files. Mutations and persisted data are untouched.
