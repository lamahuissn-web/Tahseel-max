# WhatsApp Control Center Upgrade Plan

> **For Hermes:** Use subagent-driven-development skill to implement this plan task-by-task.

**Goal:** Turn the Tahseel WhatsApp Control Center into a clear, predictable, preview-first system with one reminder engine, simpler automation UX, and safer production operations.

**Architecture:** Keep the existing Control Center entry point, but simplify it around a single reminder domain model: two automation rules, one preview contract, one send pipeline, one config shape, and one audit/log story. The UI should become explanation-first; the backend should become source-of-truth-first.

**Tech Stack:** Laravel/PHP 8.4, Blade, jQuery/SweetAlert, MariaDB `app_config` + `tbl_*`, OpenWA, existing spec-kit workflow.

---

## Current context / grounded observations

- Current reminder logic is partly centralized in `app/Services/WhatsApp/ReminderService.php`, but the service still mixes preview, filtering, sending, message building, logging, and rate delay in one class.
- `ReminderService::sendReminders()` currently fetches **all unpaid invoices** per client and does not clearly route through `InvoiceEligibilityService`, which risks logic drift from the due-date-safe paths.
- `WhatsAppControlCenterController` still holds too much domain logic: rule config storage, save/toggle/run endpoints, preview orchestration, queue controls, and UI payload shaping.
- Automation config currently stores only filter fields for `whatsapp_remind_before`; the second rule (`whatsapp_custom`) is serving as a placeholder for overdue behavior rather than a clearly named overdue rule.
- Existing route generation in `automation.blade.php` has already been corrected to use `route()` rather than `url()`, which is the right pattern and should be preserved.
- Existing specs under `.specify/specs/` include historical WhatsApp specs, but the expected file `.specify/specs/whatsapp-automation-refactor.md` is **not present**. Before implementation, a fresh spec should be written or an existing one updated so the current architecture matches reality.

---

## Desired end state

### Product behavior
- One WhatsApp Control Center with clear sections: Dashboard, Automation, Templates, Send, Log, Queue.
- Automation page centered on **two explicit rules only**:
  1. **Reminder Before Disconnection**
  2. **Overdue Reminder**
- Each rule answers 4 questions at a glance:
  - Who receives it?
  - When does it run?
  - Which template does it use?
  - How many clients are affected right now?
- Every bulk action is **preview-first**.
- Every risky action has a visible impact summary and a clear test/dry-run path.

### Technical behavior
- One reminder engine for preview + send.
- One eligibility pipeline for invoice selection.
- One stable config schema in `app_config`.
- One response contract between backend and JS.
- One audit trail for rule changes and manual runs.

---

## Risks and tradeoffs

| Risk | Why it matters | Mitigation |
|---|---|---|
| Changing automation config shape can break existing saved rules | Production automation may silently stop or mis-target | Add config migration/normalizer and keep backward-compat defaults |
| Unifying invoice logic may change who gets selected | Could reduce or expand sends unexpectedly | Compare old vs new preview counts before enabling |
| UI simplification can accidentally remove needed filters | User asked for clarity, not loss of control | Keep advanced filters under collapsible section, not deleted |
| Logging/audit additions may increase write volume | More DB inserts during bulk send | Keep payload lean; avoid storing duplicate derived data unnecessarily |
| Manual send and automation share overlapping template logic | A partial refactor can create inconsistent messages | Extract template rendering contract before changing either UI |

---

## Proposed phased approach

### Phase 1 — Stabilize the domain model
**Outcome:** clear names, clear rules, clear config structure.

- Rename placeholder rule semantics from `whatsapp_custom` to a true overdue rule in code and UI.
- Introduce a stable internal rule map:
  - `whatsapp_remind_before`
  - `whatsapp_overdue`
- Add a normalization layer so stored legacy config still loads safely.
- Define a single preview payload contract and a single send result contract.

### Phase 2 — Refactor reminder engine
**Outcome:** one source of truth for selection, preview, send, and message preparation.

- Split `ReminderService` responsibilities into smaller methods or supporting classes:
  - rule resolution
n  - invoice selection
  - preview aggregation
  - message rendering
  - send execution
  - send logging
- Route all invoice eligibility through `InvoiceEligibilityService` or a clearly expanded equivalent.
- Ensure before-disconnection and overdue rules use explicit date logic with no hidden branch behavior.

### Phase 3 — Rebuild automation UX around explanation-first controls
**Outcome:** less confusion, safer edits, better mobile operation.

- Add an always-visible rule summary card showing:
  - status
  - target scope
  - scheduled days/time
  - selected template
  - live client count
- Collapse advanced filters by default.
- Add inline helper text under each field: “Changing this will…”
- Add a “last preview result” panel for quick re-check before sending.

### Phase 4 — Add operational safety features
**Outcome:** fewer surprises in production.

- Add **dry-run** mode for rule execution.
- Add **test send** to one number before bulk run.
- Add **impact badge** (“This rule currently affects N clients / M invoices”).
- Add **emergency pause** visibility on Automation and Queue, not only elsewhere.
- Add audit entries for save/toggle/run actions.

### Phase 5 — Tighten logs and troubleshooting
**Outcome:** easier debugging and operator confidence.

- Add separate event logging for:
  - rule saved
  - rule toggled
  - preview generated
  - bulk send started
  - bulk send completed
- Distinguish “no eligible clients” from “send failed”.
- Show recent configuration changes in the UI.

---

## Files likely to change

### Backend
- Modify: `app/Services/WhatsApp/ReminderService.php`
- Modify: `app/Services/WhatsApp/InvoiceEligibilityService.php`
- Modify: `app/Http/Controllers/Admin/WhatsAppControlCenterController.php`
- Modify: `app/Console/Commands/WhatsAppRemindersCommand.php`
- Modify: `app/Console/Kernel.php`
- Modify: `app/Services/WhatsApp/WhatsAppTemplateService.php`
- Consider create: `app/Services/WhatsApp/ReminderPreviewBuilder.php`
- Consider create: `app/Services/WhatsApp/ReminderSender.php`
- Consider create: `app/Services/WhatsApp/ReminderRuleConfig.php`
- Consider create: `app/Models/WhatsAppAutomationAudit.php` or reuse existing table if a generic audit system exists

### Views / JS
- Modify: `resources/views/dashbord/whatsapp/automation.blade.php`
- Modify: `resources/views/dashbord/whatsapp/dashboard.blade.php`
- Modify: `resources/views/dashbord/whatsapp/queue.blade.php`
- Modify: `resources/views/dashbord/whatsapp/log.blade.php`
- Consider create: `resources/views/dashbord/whatsapp/_partials/rule-summary.blade.php`
- Consider create: `resources/views/dashbord/whatsapp/_partials/impact-badge.blade.php`
- Consider create: `resources/views/dashbord/whatsapp/_partials/advanced-filters.blade.php`

### Routing / translations / specs
- Modify: `routes/admin.php`
- Modify: `lang/ar/clients.php`
- Modify or create: `.specify/specs/whatsapp-control-center-upgrade.md`
- Consider plan/tasks companions under `.specify/plans/` and `.specify/tasks/`

---

## Step-by-step execution plan

### Task 1: Reconcile spec-kit docs with actual codebase

**Objective:** Ensure current implementation state is documented before more code changes.

**Files:**
- Create: `.specify/specs/whatsapp-control-center-upgrade.md`
- Optionally modify: existing WhatsApp spec files if consolidating instead of adding new spec

**Steps:**
1. Read current WhatsApp specs and identify overlap with QR, tab navigation, automation redesign, and unified system.
2. Write one current-state spec covering the real two-rule automation target.
3. Capture open issues explicitly:
   - placeholder overdue rule naming
   - reminder engine split needed
   - missing audit safety
   - preview/send contract normalization

**Validation:**
- Spec exists and reflects current code + desired end state.
- No contradictory rule names across spec files.

---

### Task 2: Introduce a stable automation rule schema

**Objective:** Make rule loading/saving explicit, backward-compatible, and future-safe.

**Files:**
- Modify: `app/Http/Controllers/Admin/WhatsAppControlCenterController.php`
- Consider create: `app/Services/WhatsApp/ReminderRuleConfig.php`

**Steps:**
1. Extract rule defaults and config normalization out of the controller.
2. Define canonical fields for both rules:
   - enabled
   - time
   - days
   - template
   - days_offset (only before-disconnection)
   - filter_client_type
   - filter_subscription_id
   - filter_min_unpaid
   - filter_client_status
3. Add a normalizer for legacy saved config where missing keys are auto-filled.
4. Rename the second rule label and internal meaning to overdue, not custom.

**Validation:**
- Existing saved config still loads.
- Both rule cards render without undefined key warnings.
- Stored JSON in `app_config.key = whatsapp_automation_rules` remains compact and readable.

---

### Task 3: Separate preview selection from send execution

**Objective:** Make preview and send deterministic and reusable.

**Files:**
- Modify: `app/Services/WhatsApp/ReminderService.php`
- Consider create: `app/Services/WhatsApp/ReminderPreviewBuilder.php`
- Consider create: `app/Services/WhatsApp/ReminderSender.php`

**Steps:**
1. Move client/invoice selection into dedicated methods per rule.
2. Standardize preview payload keys:
   - `rule_id`
   - `rule_label`
   - `description`
   - `client_count`
   - `invoice_count`
   - `total_amount`
   - `date_range`
   - `clients`
3. Ensure send execution consumes the same selected scope logic rather than rebuilding it differently.
4. Return structured send results with counts and per-client failures.

**Validation:**
- Preview output shape is identical for both rules.
- Send results are consistent whether triggered manually or by automation.

---

### Task 4: Unify invoice eligibility rules

**Objective:** Guarantee that all reminder-related sends use the same invoice selection rules.

**Files:**
- Modify: `app/Services/WhatsApp/InvoiceEligibilityService.php`
- Modify: `app/Services/WhatsApp/ReminderService.php`
- Modify: `app/Console/Commands/WhatsAppRemindersCommand.php`
- Inspect: any remaining send paths in settings/controller classes

**Steps:**
1. Audit all reminder-related invoice queries.
2. Route them through one eligibility API.
3. Explicitly handle rule differences:
   - before-disconnection = upcoming due window
   - overdue = past due only
4. Ensure message building uses the actual invoice set selected for that rule, not “all unpaid by client”.

**Validation:**
- No hidden `Invoice::where(...unpaid...)` reminder queries remain outside the central path without justification.
- Preview count matches actual send scope.

---

### Task 5: Add audit-safe operations

**Objective:** Make changes and runs traceable.

**Files:**
- Modify: `app/Http/Controllers/Admin/WhatsAppControlCenterController.php`
- Consider create: audit model/migration if no generic mechanism exists
- Modify: `resources/views/dashbord/whatsapp/log.blade.php`

**Steps:**
1. Record save/toggle/run/test actions with actor, rule, time, and summary.
2. Differentiate config events from message delivery events.
3. Surface the latest rule changes in the UI.

**Validation:**
- A rule save leaves a visible trace.
- A manual run leaves a visible trace even when zero clients qualify.

---

### Task 6: Rework automation Blade into composable partials

**Objective:** Reduce the 1200-line view burden and make future edits safer.

**Files:**
- Modify: `resources/views/dashbord/whatsapp/automation.blade.php`
- Create partials under `resources/views/dashbord/whatsapp/_partials/`

**Steps:**
1. Extract rule card markup into partials.
2. Extract advanced filters section into a partial.
3. Extract preview modal into a partial.
4. Keep all JS route generation via `route()` and locale-safe placeholders.
5. Keep comments ASCII-only to avoid Blade compilation issues.

**Validation:**
- View still renders.
- `php artisan view:clear` passes.
- No permission regressions (`chmod 644` on any new PHP/Blade files).

---

### Task 7: Add explanation-first UI copy

**Objective:** Make each setting understandable without guessing.

**Files:**
- Modify: `resources/views/dashbord/whatsapp/automation.blade.php`
- Modify: `lang/ar/clients.php`

**Steps:**
1. Add helper text beneath each critical field.
2. Add concise summary sentences above each rule.
3. Standardize translation keys with a dedicated prefix family.
4. Avoid fallback assumptions—every new key must exist in the translation file.

**Validation:**
- No raw translation keys appear in the UI.
- A non-technical admin can read the rule card and understand the effect.

---

### Task 8: Add impact summary + dry-run + test send

**Objective:** Make production actions safer.

**Files:**
- Modify: `app/Http/Controllers/Admin/WhatsAppControlCenterController.php`
- Modify: `app/Services/WhatsApp/ReminderService.php`
- Modify: `resources/views/dashbord/whatsapp/automation.blade.php`
- Possibly modify: `routes/admin.php`

**Steps:**
1. Add dry-run execution endpoint that returns what would happen without sending.
2. Add test-send path targeting one chosen number.
3. Add live impact summary in the rule card.
4. Mark broad scopes visually.

**Validation:**
- Dry-run sends zero messages.
- Test-send affects only the requested number.
- Real send still requires explicit confirmation from preview.

---

### Task 9: Harden scheduler integration

**Objective:** Ensure UI state matches cron behavior.

**Files:**
- Modify: `app/Console/Kernel.php`
- Modify: `app/Console/Commands/WhatsAppRemindersCommand.php`

**Steps:**
1. Verify schedule generation respects rule enabled/time/days config exactly.
2. Ensure disabled rules are never scheduled.
3. Add safe logging around cron execution start/finish.
4. Add clear output when no clients are eligible.

**Validation:**
- Scheduler reflects saved config.
- Manual preview numbers reasonably match cron-selected scope for the same day.

---

### Task 10: Final cleanup and verification

**Objective:** Finish with a stable, operator-friendly control center.

**Files:**
- All touched files above

**Steps:**
1. Run syntax checks.
2. Clear Laravel caches.
3. Verify permissions on new files.
4. Compare before/after rule behavior on known sample clients.
5. Review mobile layout with screenshot evidence.

**Validation commands:**
- `php -l app/Services/WhatsApp/ReminderService.php`
- `php -l app/Http/Controllers/Admin/WhatsAppControlCenterController.php`
- `php artisan view:clear`
- `php artisan config:clear`
- `php artisan cache:clear`
- Targeted tinker / route checks for preview counts and config load

---

## Suggested order of implementation

| Order | Change | Why |
|---|---|---|
| 1 | Spec reconciliation | Prevents building on stale assumptions |
| 2 | Rule schema cleanup | Foundation for everything else |
| 3 | Reminder engine split | Biggest reliability gain |
| 4 | UI simplification | Biggest operator clarity gain |
| 5 | Safety features | Production protection |
| 6 | Audit/log improvements | Better troubleshooting |
| 7 | Scheduler hardening | Final consistency pass |

---

## Success criteria

The upgrade is successful when all of the following are true:

- The automation page shows exactly two clearly named rules.
- The user can predict the effect of a setting change without opening code.
- Preview, dry-run, manual run, and cron all use the same selection rules.
- The control center no longer depends on placeholder rule semantics.
- There is a visible audit trail for configuration changes and executions.
- New Blade files render cleanly and keep correct permissions.
- No route/locale regressions occur.
- No raw translation keys appear.

---

## Recommendation

Implement this as **small safe phases**, not one large rewrite.

**Best first execution slice:**
1. Spec reconciliation
2. Stable rule schema
3. Reminder engine cleanup

That gives the highest reliability improvement with manageable UI risk.
