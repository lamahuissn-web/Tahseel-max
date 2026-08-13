# Feature 016 — WhatsApp Batch Control and Queue UX

## Problem
Tahseel’s queue page infers batches from `whatsapp_message_logs.sent_by`, mixes operational and historical messages, and labels the automation toggle as a queue pause. Administrators cannot safely cancel remaining messages in one batch or archive completed history.

## Safety boundary
This feature MUST NOT change the financial/payment decision path or suppress automatic payment receipts.

- A successfully committed invoice payment continues to invoke `PaymentReceiptNotifier` exactly as before.
- A receipt intent/log remains durable and is dispatched on the dedicated WhatsApp database queue.
- Cancelling batch A must not cancel, pause, relabel, or otherwise affect an unrelated automatic receipt in batch B.
- A messaging failure must never change or roll back payment state.
- No automated test may call live OpenWA or perform a real financial write.

## Functional requirements

### FR-001 Real batches
New WhatsApp work is associated with a durable `whatsapp_batches` record and an optional `whatsapp_message_logs.batch_id` foreign key. Existing `sent_by|batch:<uuid>` data remains readable as legacy history.

A batch records UUID, source, title, template type, lifecycle status, creator, cancellation metadata, archival metadata, and timestamps.

### FR-002 Lifecycle
Batch statuses are explicit: `queued`, `running`, `completed`, `completed_with_errors`, `cancelling`, `cancelled`. Archival is represented separately by `archived_at`.

Message statuses are explicit: `pending`, `sending`, `sent`, `failed`, `cancelled`.

### FR-003 Cancel remaining
Cancelling a batch is transactional and idempotent:

1. Lock/re-read the batch.
2. Record actor, time, and optional bounded reason.
3. Change only that batch’s `pending` messages to `cancelled`.
4. Preserve `sending`, `sent`, and `failed` rows unchanged.
5. Return committed counts by message status.

The preview/confirmation states that sent messages cannot be recalled and sending messages may complete.

### FR-004 Worker enforcement
Immediately before any OpenWA call, the worker re-reads durable message and batch state. It safely no-ops for missing, cancelled, or archived-ineligible work. Eligibility must not be based only on removal of queue rows.

A message already in `sending` is never blindly converted to `cancelled`; ambiguous delivery protection remains active.

### FR-005 Global delivery pause
A dedicated `whatsapp_queue_paused` setting controls starting pending deliveries. It is separate from `whatsapp_auto_enabled`.

- Pausing does not disable receipt creation or unrelated automation configuration.
- Pending jobs remain durable and retry later without calling OpenWA.
- A message already in an external provider call may finish.
- Resume allows pending delivery to continue.

### FR-006 Archive and deletion
Completed/cancelled batches can be archived idempotently. Archiving hides them from the default active/recent view but preserves all messages and audit evidence.

Normal UI must not physically delete a batch containing any message or delivery evidence. Hard deletion is out of scope.

### FR-007 Retry
Retry is scoped to one batch and only eligible failed messages. It must not automatically reopen a cancelled batch or retry ambiguous failures without explicit operator acknowledgement. The existing global retry-all action is removed from the primary UI.

### FR-008 Queue UX
The queue page is batch-first and Arabic/RTL compatible. It shows:

- global state: running/paused;
- active batches and remaining message totals;
- failures today;
- batch title/source/creator/created time;
- counts: total, pending, sending, sent, failed, cancelled;
- progress and derived status;
- actions only when eligible: details, cancel remaining, retry failed, archive;
- individual messages as secondary detail with status/source/date filters.

Counts are computed from durable rows, not a fixed recent-row sample.

### FR-009 Authorization and audit
All mutation routes require the existing authenticated admin group plus explicit authorization consistent with the control center. Cancellation, pause/resume, retry, and archive record actor and time in the existing activity log when available. Inputs are validated; reasons are bounded.

### FR-010 Backward compatibility
Legacy producers and messages continue to work while migrated incrementally. If a producer still supplies only `sent_by|batch:<uuid>`, the dispatcher/batch service resolves or creates the corresponding durable batch without changing message content or payment behavior.

## Acceptance criteria

1. Payment receipt creation test passes before and after changes and produces one pending durable message/job.
2. Successful receipt delivery still leaves invoice notification metadata unchanged.
3. Pausing delivery does not prevent receipt log/job creation, but worker does not call OpenWA until resumed.
4. Cancelling a manual batch does not affect an unrelated receipt batch.
5. Cancel preview and commit report accurate counts; only pending rows become cancelled.
6. Worker cannot call OpenWA for a cancelled message/batch even if its queue job is already reserved.
7. Repeated cancel/archive/pause requests are stable and safe.
8. Completed/sent history cannot be hard-deleted through normal routes.
9. Legacy messages without `batch_id` remain visible and dispatchable.
10. Focused WhatsApp tests, migration tests, syntax, diff integrity, and independent audit pass.
