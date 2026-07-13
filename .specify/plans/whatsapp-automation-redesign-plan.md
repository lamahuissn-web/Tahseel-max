# Plan: WhatsApp Automation Redesign

## Phases

### Phase 1: Backend — Routes + Controller
1.1 Add 5 new routes to routes/admin.php
1.2 Add controller methods:
    - getAutomationClients() — filtered client list
    - sendNow() — immediate send
    - scheduleTask() — create new scheduled rule
    - deleteTask() — delete a rule
    - getAutomationTasks() — rules with stats
1.3 Modify existing automation() to pass tasks data

### Phase 2: Frontend — View Restructure
2.1 Rewrite automation.blade.php with 3-tab layout
2.2 Build Tab 1 (Customers): filters + table + action bar + send modal
2.3 Keep Tab 2 (Calendar): as-is
2.4 Build Tab 3 (Scheduled Tasks): cards + toggle + edit modal + delete

### Phase 3: Polish
3.1 Responsive design for all 3 tabs
3.2 Test all flows
3.3 Commit
