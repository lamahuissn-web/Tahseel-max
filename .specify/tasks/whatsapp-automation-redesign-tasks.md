# Tasks: WhatsApp Automation Redesign

## Phase 1: Backend
- [ ] 1.1 Add 5 routes (clients, send-now, schedule-task, delete-task, tasks)
- [ ] 1.2 Add getAutomationClients() — reuse broadcast preview logic
- [ ] 1.3 Add sendNow() — reuse calendarSend logic
- [ ] 1.4 Add scheduleTask() — create rule in app_config
- [ ] 1.5 Add deleteTask() — remove rule from app_config
- [ ] 1.6 Add getAutomationTasks() — rules + log stats
- [ ] 1.7 Update automation() to pass tasks data

## Phase 2: Frontend
- [ ] 2.1 Rewrite automation.blade.php with 3 tabs
- [ ] 2.2 Tab 1 (Customers): filters + table + actions
- [ ] 2.3 Tab 2 (Calendar): keep as-is
- [ ] 2.4 Tab 3 (Tasks): cards + toggle + edit modal + delete
- [ ] 2.5 Send modal + schedule modal

## Phase 3: Polish
- [ ] 3.1 Responsive CSS for all tabs
- [ ] 3.2 Test all flows (filter, send, schedule, toggle, delete)
- [ ] 3.3 Commit
