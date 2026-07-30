# WhatsApp Serialized Delivery — Tasks

- [x] T001 Add RED tests for receipt enqueueing without `exec()`.
- [x] T002 Add RED tests for latest Revenue amount/collector selection.
- [x] T003 Add RED tests for unique job status, retry, and idempotency behavior.
- [x] T004 Add RED test for global atomic send locking.
- [x] T005 Add jobs migration and dedicated database queue connection.
- [x] T006 Implement `SendWhatsAppMessage` job.
- [x] T007 Refactor `PaymentReceiptNotifier` to dispatch after commit.
- [x] T008 Implement pending recovery command and one-minute timer.
- [x] T009 Add global lock to `WhatsAppService::send()`.
- [x] T010 Add systemd worker and recovery artifacts.
- [x] T011 Run targeted tests, full safe test suite, syntax checks, migration smoke test, and 20-process lock test.
- [ ] T012 Run clean-code and test guard review; fix findings.
- [ ] T013 Deploy migration and worker, then verify live queue health without sending customer messages.
