# Plan — Feature 016

## Architecture

Introduce a compatibility-first batch layer without rewriting financial producers:

- `WhatsAppBatch` is durable operational metadata.
- `WhatsAppMessageLog.batch_id` is nullable for legacy compatibility.
- `WhatsAppBatchService` resolves legacy provenance, computes summaries, previews/commits cancellation, archive, and retry eligibility.
- `WhatsAppQueueState` owns only the dedicated global delivery pause key.
- `SendWhatsAppMessage` performs final eligibility checks before provider status/send calls.
- Existing `PaymentReceiptNotifier` remains responsible for creating receipt logs; compatibility is tested at its public boundary.

## Migration strategy

1. Create `whatsapp_batches` with indexes and no destructive data conversion.
2. Add nullable indexed `batch_id` to message logs and extend status enum with `cancelled`.
3. New/redispatched work resolves durable batches from existing provenance.
4. Legacy rows continue to render through a fallback parser.
5. Rollback nulls/drops FK first, converts cancelled messages conservatively to failed, restores old enum, then drops batch table.

## TDD tracer bullets

1. RED→GREEN: receipt still creates one durable pending log/job.
2. RED→GREEN: batch creation/resolution associates new logs without changing `sent_by`.
3. RED→GREEN: cancellation changes only pending rows and returns committed counts.
4. RED→GREEN: worker no-ops for cancelled work before OpenWA.
5. RED→GREEN: unrelated receipt survives cancellation of another batch.
6. RED→GREEN: dedicated pause prevents provider calls but preserves pending job/log.
7. RED→GREEN: archive is idempotent and sent batches cannot be deleted.
8. RED→GREEN: batch-first controller summaries and eligible actions.
9. RED→GREEN: Arabic responsive Blade renders required states/actions.

## Verification

- Focused Feature 016 tests.
- Existing `WhatsAppStabilityTest`, especially receipt cases.
- Relevant payment/controller tests that trigger receipt notification.
- Migration up/down on an isolated SQLite/MySQL-compatible test schema where feasible.
- PHP syntax, Blade compilation, route list, `git diff --check`.
- No live OpenWA call.
- Fresh-context independent audit with PASS/BLOCK.

## Deployment boundary

Do not run migrations, restart workers, switch the active development checkout, commit, push, or merge until tests and independent audit pass and KIRA approves the test deployment.
