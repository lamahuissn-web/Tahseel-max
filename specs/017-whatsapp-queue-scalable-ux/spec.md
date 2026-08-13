# Feature 017 — Scalable WhatsApp Queue UX

## Goal
Keep the WhatsApp queue operationally useful with hundreds of batches and messages on desktop and mobile without changing any queue mutation or delivery safety behavior.

## Requirements
- The default page renders queue summary, filters, section counts, and one compact batch paginator only. It must not query or render global message details.
- Batch sections are `attention` (default), `completed`, and `cancelled`. Attention includes batch statuses `queued`, `running`, `cancelling`, `completed_with_errors`, or any pending/sending/failed message.
- Apply source, date, message-status, and section filters to the bounded batch list. Render 10 batches per `batches_page` with a visible result range.
- Each batch row is compact (roughly 80–100px), with one operational counts/progress line and consolidated actions; do not render six permanent count badges.
- Details load only for a valid, unarchived selected batch that remains valid under source/date/section filters. Detail messages belong only to that batch and honor message status/date filters.
- Detail messages paginate 10 per `messages_page`, independently from batch pagination.
- Mobile details use polished cards with customer, masked phone, Arabic status/source, and date. Technical provenance is hidden by default in `<details>`.
- Desktop details use a compact responsive table over the same 10-row collection.
- Details/back links preserve filters and batch-page state. Back removes `batch` and `messages_page`; details links anchor to `#messages`.
- Legacy groups remain compatible but bounded/collapsed; they must never create a 50-row default table.
- No DataTables.

## Non-goals / safety
- No mutation-route, queue service, payment, worker, OpenWA, migration, or external-send changes.
- No commit, push, deployment, live database access, or worker execution.

## Acceptance criteria
- Focused tests prove no default detail query/data, 10-row parent/child pagination, operational section classification/counts, selected-batch scoping, responsive render markers, masked phone, hidden provenance, and stable query parameters.
- Existing WhatsApp feature suite remains green.
- PHP syntax, Blade view cache, rendered inline JavaScript parsing, and diff checks pass.
