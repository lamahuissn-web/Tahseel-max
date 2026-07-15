# Tasks: WhatsApp Automation Refactor

---

## Phase 1: ReminderService

- [ ] **1.1** Create `app/Services/WhatsApp/ReminderService.php`
  - `getBeforeDisconnectionPreview($days, $filters)` — returns clients + invoices
  - `getOverduePreview($filters)` — returns clients with past-due invoices
  - `sendReminder($ruleId, $clientIds)` — sends with 10s delay
  - `getFilteredClients($filters)` — applies client type, subscription, status filters

## Phase 2: Controller

- [ ] **2.1** Add `previewAutomationRule($id)` to controller
- [ ] **2.2** Simplify `runAutomationRule($id)` to use ReminderService
- [ ] **2.3** Remove duplicate query code from controller

## Phase 3: View

- [ ] **3.1** Rewrite `automation.blade.php` with 2 rule cards
- [ ] **3.2** Add preview modal (shows client list, amounts, count)
- [ ] **3.3** Add filter section with clear labels + "All" default
- [ ] **3.4** Wire JavaScript: Preview → Modal → Confirm → Send

## Phase 4: Test

- [ ] **4.1** Test preview with no filters (all clients)
- [ ] **4.2** Test preview with filters
- [ ] **4.3** Test send from preview
- [ ] **4.4** Test calendar tab still works
