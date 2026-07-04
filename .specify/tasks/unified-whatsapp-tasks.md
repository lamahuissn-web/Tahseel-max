# Tasks: Unified WhatsApp Message System

**Input**: `.specify/specs/unified-whatsapp-system.md`
**Plan**: `.specify/plans/unified-whatsapp-plan.md`

---

## Phase 1: WhatsAppMessageBuilder

- [ ] T001 [P] Update `buildInvoiceDetailsList()` — change format to `❌ MM / YYYY      $amount` (no Arabic months)
- [ ] T002 [P] Remove `$arabicMonths` array from `WhatsAppMessageBuilder`
- [ ] T003 Update `defaultTemplate()` — add MegaNet branding header + footer

## Phase 2: WhatsAppRemindersCommand

- [ ] T004 Remove duplicate `$arabicMonths` array from `WhatsAppRemindersCommand` (lines 24-30)
- [ ] T005 Update `displayPreview()` — remove Arabic month names, use MM/YYYY

## Phase 3: WhatsAppSettingsController

- [ ] T006 Update `preview()` — remove invoice numbers from sample, use MM/YYYY format

## Phase 4: Verify

- [ ] T007 Run `php -l` on all modified files
- [ ] T008 Run `php artisan whatsapp:reminders` (preview mode) — check output
- [ ] T009 Send test reminder to Kira's number to verify format

---

## Execution Order

```
T001 ─→ T003 ─→ T004 ─→ T005 ─→ T006 ─→ T007 ─→ T008 ─→ T009
    ↕
   T002 (parallel with T001)
```

**Commit message**: `feat: unify WhatsApp messages under MegaNet brand identity`
