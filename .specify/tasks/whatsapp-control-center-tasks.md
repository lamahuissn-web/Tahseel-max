# Tasks: WhatsApp Control Center

**Input**: `.specify/specs/whatsapp-control-center.md`
**Plan**: `.specify/plans/whatsapp-control-center-plan.md`

---

## Phase 1A — Database + Model

- [ ] T001 Create migration `create_whatsapp_message_logs_table` — columns: id, client_id, client_name, client_phone, template_type, message_body, status (sent/failed/pending), error_message, sent_by, created_at
- [ ] T002 Create model `WhatsAppMessageLog` — fillable, casts (status enum), relationship to Client
- [ ] T003 Add default template keys to `app_config` on first migration or seeder (receipt, reminder, disconnection warning)
- [ ] T004 Run `php artisan migrate` on CT 131

## Phase 1B — Routes + Sidebar

- [ ] T005 Create route group `admin/whatsapp` with auth + admin middleware — all tab routes
- [ ] T006 Add GET routes: dashboard, templates, send, log views
- [ ] T007 Add POST routes: templates/save, templates/test, send/broadcast, send/search-clients, log/{id}/resend
- [ ] T008 Add P2 routes (placeholder): automation, queue views + their POST actions
- [ ] T009 Update sidebar — replace settings/whatsapp link with `📱 WhatsApp Control Center` link to `/admin/whatsapp/dashboard`

## Phase 1C — Dashboard Tab

- [ ] T010 Create `WhatsAppControlCenterController` with `dashboard()` method — queries for: OpenWA connection status, messages today, messages this month, failures today, client count with WhatsApp numbers, last successful send
- [ ] T011 Create `dashboard.blade.php` — stat cards grid (6-8 cards), emergency state banner, quick action buttons (Emergency Stop + Restart Service), recent activity row
- [ ] T012 Add translations for dashboard UI elements in `lang/ar/clients.php`

## Phase 1D — Templates Tab

- [ ] T013 Create `templates()` method — fetch template bodies from `app_config`, render edit view
- [ ] T014 Create `templates.blade.php` — list of templates (accordion or tabs), each with textarea + variable buttons + live preview
- [ ] T015 Create variable buttons component — `{name}`, `{amount}`, `{date}`, `{phone}` insert at cursor position (JS)
- [ ] T016 Create `saveTemplate()` method — validate + save to `app_config`
- [ ] T017 Create `testTemplate()` method — send sample to provided phone number via existing WhatsAppService
- [ ] T018 Add fallback logic: if template key missing from `app_config`, load hardcoded default
- [ ] T019 Add translations for templates UI

## Phase 1E — Message Log Tab

- [ ] T020 Create `log()` method — render view, pass initial data
- [ ] T021 Create `logData()` method — server-side DataTable endpoint: searchable by name/phone, filterable by date range / status / template type
- [ ] T022 Create `log.blade.php` — DataTable with columns: Client Name, Phone, Template, Status (✅/❌), Sent At, Actions; row expansion modal for full message body
- [ ] T023 Create `resendMessage()` method — re-send specific message via WhatsAppService + update log entry status
- [ ] T024 Add translations for log UI

## Phase 1F — Send Tab (Broadcast)

- [ ] T025 Create `send()` method — render view with template dropdown + filter form
- [ ] T026 Create `searchClients()` method — API endpoint: search `tbl_clients` by name/phone/ID, return JSON for Select2
- [ ] T027 Create `send.blade.php` — dual-mode interface: manual search/pick + smart filter panel
- [ ] T028 Implement manual selection UI — Select2 typeahead + removable chips for selected clients
- [ ] T029 Implement smart filter panel — unpaid bills count (>= N), area/district, subscription type, last payment date before, has WhatsApp number (auto-checked)
- [ ] T030 Implement combined flow — filter → manual add/remove → preview count → send
- [ ] T031 Create `broadcast()` method — validate, loop through recipients, call WhatsAppService->sendMessage(), log each to `whatsapp_message_log`, rate-limit 1s between sends, return result summary
- [ ] T032 Add translations for send UI

## Phase 2A — Automation Tab (P2)

- [ ] T033 Create `automation()` method — read automation rules (from `app_config` or parse Kernel schedule), render view
- [ ] T034 Create `automation.blade.php` — table of rules: Name, Status 🟢/⚪/🔴, Last Run, Next Run, Frequency, Actions (Toggle, Run Now)
- [ ] T035 Create `toggleAutomationRule()` method — update rule status in `app_config`
- [ ] T036 Create `runAutomationRule()` method — `Artisan::call()` the corresponding command, log output
- [ ] T037 Add translations for automation UI

## Phase 2B — Queue Tab (P2)

- [ ] T038 Create `queue()` method — query `whatsapp_message_log` for pending/recent items
- [ ] T039 Create `queue.blade.php` — status counts (Pending / Sending / Failed), recent items table, Resend All Failed button, Pause/Resume toggle
- [ ] T040 Create `resendAllFailed()` + `toggleQueuePause()` methods
- [ ] T041 Add translations for queue UI

## Integration — Logging Existing Sends

- [ ] T042 Update `PaymentReceiptNotifier::sendReceipt()` — after successful send, insert row into `whatsapp_message_log`
- [ ] T043 Update `WhatsAppRemindersCommand::handle()` — after each reminder send, insert row into `whatsapp_message_log`
- [ ] T044 Update emergency kill switch — log emergency stop event (template_type = 'emergency_stop') to `whatsapp_message_log`

## Verification

- [ ] T045 Run `php -l` on all modified PHP files — no syntax errors
- [ ] T046 Run `php artisan route:list` — confirm all new routes registered
- [ ] T047 Open Dashboard tab in browser — stat cards show real data
- [ ] T048 Open Templates tab — edit text → save → preview updates correctly
- [ ] T049 Send test template to own number — message arrives on WhatsApp
- [ ] T050 Open Message Log — see logged sends, search by name works
- [ ] T051 Send to single client via Send tab — client receives message, log updated
- [ ] T052 Send to filtered group — filter works, count preview correct
- [ ] T053 Resend a failed message — message re-sends, log status updates
- [ ] T054 Trigger a payment receipt — auto-receipt still works and appears in log
- [ ] T055 Run `php artisan whatsapp:reminders` — cron still works, appears in log
- [ ] T056 Hit Emergency Stop — WhatsApp stops, event logged
- [ ] T057 Check with Kira — open all tabs, confirm everything feels right

---

## Execution Order

```
T001 → T002 → T003 → T004
    │
    ▼
T005 → T006 → T007 → T008 → T009
    │
    ├──→ T010 → T011 → T012
    ├──→ T013 → T014 → T015 → T016 → T017 → T018 → T019
    ├──→ T020 → T021 → T022 → T023 → T024
    └──→ T025 → T026 → T027 → T028 → T029 → T030 → T031 → T032
                │
                ▼
          T042 → T043 → T044 (parallel — update existing flows to log)
                │
                ▼
          T033 → T034 → T035 → T036 → T037 (P2)
                │
                ▼
          T038 → T039 → T040 → T041 (P2)
                │
                ▼
          T045 → T046 → ... → T057 (verification)
```

## Commits

| Phase | Commit Message |
|:-----:|----------------|
| 1A | `feat: add whatsapp_message_log table and model` |
| 1B | `feat: restructure WhatsApp routes under /admin/whatsapp/*` |
| 1C | `feat: WhatsApp Control Center dashboard with health cards` |
| 1D | `feat: editable WhatsApp message templates with live preview` |
| 1E | `feat: WhatsApp message log with search and resend` |
| 1F | `feat: WhatsApp broadcast send with manual pick and smart filter` |
| 2A | `feat: WhatsApp automation rule management` |
| 2B | `feat: WhatsApp queue status panel` |
| Integration | `feat: log all WhatsApp sends to message log` |
| Final | `chore: verify WhatsApp Control Center complete` |
