# WhatsApp Serialized Delivery Stability

## Goal
Keep WhatsApp receipt delivery safe and durable when many collectors pay invoices at the same time, without changing invoice or accounting behavior.

## User stories
- As a collector, payment completion must not wait for WhatsApp delivery.
- As an operator, receipt messages must be processed one at a time and remain recoverable after a process or server restart.
- As an operator, all WhatsApp send paths must be protected from simultaneous OpenWA requests.
- As a customer, a partial-payment receipt must show the amount and collector from the latest payment record.

## Functional requirements
1. Payment receipts are persisted as `pending` before dispatch.
2. Receipt delivery uses Laravel's durable database queue, not detached `exec()` processes.
3. A dedicated worker processes the `whatsapp` queue with concurrency one.
4. Every OpenWA text send is guarded by one atomic application-wide lock.
5. Rate-limited and temporarily disconnected sends are retried with backoff; the message remains visible as pending.
6. Permanent failures are marked failed with an actionable error.
7. Pending batch records not represented by an active job are redispatched automatically and idempotently; a failed queue insertion must release its uniqueness lock immediately.
8. Receipt content uses the newest Revenue record's amount, collector, and time.
9. Existing Queue and Safety dashboards continue using `whatsapp_message_logs`.
10. Automated tests must never contact the live OpenWA endpoint.
11. Long batch pauses are persisted as retry times and never sleep while holding the global send lock.
12. Permanent OpenWA 4xx errors fail immediately; transient failures retry for up to 24 hours.
13. A stale `sending` record or uncertain OpenWA transport result is marked ambiguous instead of being automatically resent and potentially duplicated.
14. Legacy pending batches and dashboard resend actions dispatch to the same durable queue.

## Non-goals
- Invoice calculation, payment validation, account balances, and same-invoice payment concurrency.
- UI redesign.
- Sending a real customer test message.
- Converting every legacy direct sender to queue jobs in this change.

## Acceptance criteria
- Twenty rapidly enqueued receipts create durable jobs without spawning detached PHP processes.
- The worker sends only one message at a time.
- A second concurrent direct sender cannot enter OpenWA while the first holds the lock.
- Worker restart does not lose queued jobs.
- A disconnected session retries instead of immediately losing the receipt.
- Rate-limit pauses preserve pending status and retry at the provided time.
- Batch pauses apply globally after each configured number of sent messages without holding the send lock.
- A worker interruption during an OpenWA request cannot cause an automatic duplicate resend.
- A failed database-queue insertion cannot strand a pending message behind a leaked uniqueness lock.
- Latest partial-payment Revenue details appear in the receipt.
- Relevant automated tests, syntax checks, and queue smoke tests pass.
