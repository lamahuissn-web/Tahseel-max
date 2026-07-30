# WhatsApp Serialized Delivery — Implementation Plan

## Architecture
Use Laravel's database queue as a durable receipt outbox. `PaymentReceiptNotifier` creates the existing message log record and dispatches a unique `SendWhatsAppMessage` job after commit. A single systemd-managed worker consumes queue `whatsapp`. `WhatsAppService::send()` uses a cache atomic lock as a safety boundary for queued and legacy direct sends.

## Changes
1. Add Laravel `jobs` table migration and a dedicated `whatsapp_database` queue connection with a safe retry window.
2. Add `SendWhatsAppMessage` unique job with retry/backoff/status transitions.
3. Add `RecoverPendingWhatsAppMessages` command plus a one-minute systemd recovery timer.
4. Replace receipt notifier process spawning with after-commit job dispatch.
5. Use latest Revenue amount/collector/time in receipt content.
6. Put Rate Limiter approval and OpenWA request behind a global atomic lock; represent long batch pauses as cached retry deadlines so the lock is never held during a multi-minute pause.
7. Add systemd worker/recovery unit templates to the repository and install them only during rollout.
8. Add unit/feature tests using Queue/HTTP fakes and isolated fixtures.
9. Redirect legacy pending-batch processors and dashboard resend actions to the durable queue.

## Failure semantics
- Rate limited: set pending and release job using `retry_after_seconds`.
- OpenWA disconnected/transient network failure: set pending and retry until the fixed 24-hour job deadline.
- Permanent OpenWA 4xx failure: mark failed immediately without wasting retries.
- Stale `sending` after worker interruption: mark failed as ambiguous and require manual verification before resend.
- Retry deadline reached: mark failed with the final exception message.
- Already sent: job exits idempotently.
- Duplicate dispatch: `ShouldBeUnique` uses message-log ID.

## Deployment
1. Back up affected source files and database schema.
2. Run only the new jobs migration.
3. Install/reload/start the systemd worker and recovery timer.
4. Clear Laravel caches.
5. Verify worker, timer, queue table, pending recovery, and fake-provider tests.

## Rollback
Stop/disable the worker and revert the branch. Receipt records remain in `whatsapp_message_logs`; the `jobs` table may remain harmlessly. No invoice or accounting schema is changed.
