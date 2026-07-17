# Feature Specification: WhatsApp Control Center Upgrade

**Feature Branch**: `feature/whatsapp-control-center`

**Created**: 2026-07-17 | **Updated**: 2026-07-17

**Status**: Draft

**Input**: User request to make the WhatsApp Control Center clearer, safer, and easier to operate daily. Current pain is concentrated in the Automation area: the user wants to know exactly what happens when a setting changes, wants preview before sending, and wants a simpler mental model with strong production safety.

---

## Problem Statement

The WhatsApp Control Center is already valuable, but it still behaves like a collection of features rather than one coherent operations system.

### Current strengths

| Area | Current value |
|---|---|
| Dashboard | Shows connection and activity pulse |
| QR recovery | Re-authenticate WhatsApp from inside Tahseel |
| Templates | Editable message bodies exist |
| Automation | Rule cards, preview, calendar, and manual run exist |
| Logs / Queue | Operational visibility exists |

### Current problems

| Problem | Why it hurts |
|---|---|
| Automation rules are not self-explanatory | The user cannot predict the effect of a change confidently |
| Rule naming is still inconsistent | `whatsapp_custom` is effectively acting like overdue reminder logic |
| Reminder engine is partially centralized only | Preview/send/cron can drift over time |
| Controller owns too much domain logic | Harder to maintain, test, and reason about |
| `automation.blade.php` is too large | High risk for regressions and hard to edit safely |
| Bulk operations still need stronger safety rails | Production sends should be preview-first, impact-aware, and auditable |

**Goal**: Upgrade the WhatsApp Control Center into a clear, predictable, preview-first system with a stable two-rule automation model, one reminder engine, and stronger operator safety.

---

## Product Principles

1. **One source of truth** — same selection logic for preview, manual run, and cron.
2. **Explanation first** — the admin should understand what a setting changes without reading code.
3. **Preview before send** — every bulk action should show target scope first.
4. **Safe by default** — production-risk actions need warnings, test path, or dry-run.
5. **Simple mental model** — two main reminder rules, not many overlapping concepts.

---

## User Stories

### User Story 1 — Clear Automation Rules (Priority: P1)

As an admin, I want the automation page to show only the important reminder rules in a clear way, so I can understand them quickly and trust what they will do.

**Acceptance Criteria:**
1. Automation page shows exactly two primary rules:
   - Reminder Before Disconnection
   - Overdue Reminder
2. Each rule card clearly states:
   - what it sends
   - who it targets
   - when it runs
   - which template it uses
3. Each rule card includes a human-readable summary sentence.
4. The system does not expose placeholder wording like “custom” when the real behavior is overdue reminder logic.

---

### User Story 2 — Predictable Settings Impact (Priority: P1)

As an admin, I want to know what happens when I change a setting, so I can edit rules without confusion.

**Acceptance Criteria:**
1. Critical controls have helper text explaining their effect.
2. Rule cards show a live summary of current scope.
3. Advanced filters are available but visually separated from the basic controls.
4. A change in time, days, template, or filters updates the rule summary clearly.

---

### User Story 3 — Preview-First Sending (Priority: P1)

As an admin, I want preview before any bulk send, so I can verify the target clients and invoices before messages go out.

**Acceptance Criteria:**
1. Preview is available for both automation rules.
2. Preview shows:
   - client count
   - invoice count
   - total amount
   - client list
   - date context / rule description
3. The send path uses the same selected scope as the preview.
4. The user must confirm before bulk send executes.

---

### User Story 4 — Safer Production Operations (Priority: P1)

As an admin, I want stronger operational safety, so mistakes do not reach customers unexpectedly.

**Acceptance Criteria:**
1. The system supports dry-run mode for automation rules.
2. The system supports test-send to a single chosen number.
3. The UI visibly highlights broad-impact actions.
4. Emergency pause is easy to see from the operational screens.
5. A run with zero eligible clients is reported clearly, not treated like an error.

---

### User Story 5 — Unified Reminder Engine (Priority: P1)

As a maintainer, I want one reminder engine for selection, preview, and sending, so behavior stays consistent and bugs are easier to fix.

**Acceptance Criteria:**
1. Reminder logic is routed through one core engine/service flow.
2. Invoice eligibility rules are centralized.
3. Manual preview, manual run, and scheduled cron use the same underlying rule selection logic.
4. The system avoids duplicate “unpaid invoice” queries scattered across paths without explicit justification.

---

### User Story 6 — Better Change Traceability (Priority: P2)

As an admin, I want to know who changed a rule or ran it, so troubleshooting is easier.

**Acceptance Criteria:**
1. Saving a rule creates an audit entry.
2. Toggling a rule creates an audit entry.
3. Manual run / dry-run / test-send create audit entries.
4. Recent operational events are visible in the log view or a dedicated activity area.

---

## Functional Requirements

### Rule model
- **FR-001**: The system MUST expose exactly two primary automation rules for reminders.
- **FR-002**: The rule IDs MUST be stable and semantically correct.
- **FR-003**: The overdue reminder rule MUST NOT be represented as a generic “custom” rule in the UI.
- **FR-004**: Rules MUST load from a normalized config structure with backward-compatible defaults.

### Preview and send
- **FR-005**: The system MUST support preview for both primary reminder rules.
- **FR-006**: Preview response MUST use one stable JSON contract for all rule types.
- **FR-007**: Send execution MUST use the same selection scope as preview.
- **FR-008**: Bulk send MUST require explicit confirmation after preview.

### Safety
- **FR-009**: The system MUST support dry-run for automation rules.
- **FR-010**: The system MUST support test-send to a single number.
- **FR-011**: The UI MUST display a visible impact summary before send.
- **FR-012**: The system MUST distinguish “no eligible clients” from “failed to send”.

### Observability
- **FR-013**: The system MUST record operational events for save, toggle, preview, run, and dry-run actions.
- **FR-014**: Logs MUST show recent reminder execution outcomes clearly.

### Maintainability
- **FR-015**: Reminder selection logic MUST be centralized.
- **FR-016**: View structure for automation MUST be decomposed into safer partials where practical.
- **FR-017**: Translation keys added for this feature MUST follow a consistent WhatsApp naming convention.

---

## Non-Functional Requirements

- **NFR-001**: Backward compatibility with existing `app_config` automation data is required.
- **NFR-002**: No locale-prefix regressions are allowed in AJAX endpoints; route generation must remain `route()`-based.
- **NFR-003**: New Blade files must remain ASCII-safe in comments to avoid compilation issues.
- **NFR-004**: New PHP/Blade files created by Hermes must be set to permissions Apache can read (`644`).
- **NFR-005**: Existing working features — QR reconnect, tabs, preview modal flow, calendar access — must not regress.

---

## Target UX Structure

### WhatsApp Control Center tabs

```text
WhatsApp Control Center
├── Dashboard
├── Templates
├── Send
├── Automation
├── Log
└── Queue
```

### Automation page structure

```text
Automation
├── Rule Card: Reminder Before Disconnection
│   ├── status
│   ├── schedule
│   ├── template
│   ├── scope summary
│   ├── basic controls
│   ├── advanced filters (collapsed by default)
│   ├── preview
│   ├── dry-run / test-send
│   └── save / toggle / run
├── Rule Card: Overdue Reminder
│   └── same interaction pattern
└── Calendar tab or section (kept)
```

---

## Technical Direction

### Current implementation facts

| Area | Current state |
|---|---|
| Reminder engine | `app/Services/WhatsApp/ReminderService.php` exists but mixes multiple responsibilities |
| Controller | `WhatsAppControlCenterController` still owns config + orchestration + response shaping |
| Config | rules stored in `app_config.key = whatsapp_automation_rules` |
| View | `automation.blade.php` is large and risky to edit directly |
| Scheduling | `Kernel.php` schedules `whatsapp:reminders --send --rule={id}` |
| Safety | preview exists, but dry-run/test-send/audit need improvement |

### Directional changes

| File | Expected change |
|---|---|
| `app/Services/WhatsApp/ReminderService.php` | Split or refactor into cleaner single-responsibility flow |
| `app/Services/WhatsApp/InvoiceEligibilityService.php` | Keep or extend as central invoice-rule source |
| `app/Http/Controllers/Admin/WhatsAppControlCenterController.php` | Reduce domain/config burden; keep thin endpoints |
| `resources/views/dashbord/whatsapp/automation.blade.php` | Simplify UI and split to partials |
| `routes/admin.php` | Add dry-run/test-send/audit-safe endpoints if needed |
| `lang/ar/clients.php` | Add consistent helper/summary text keys |
| `app/Console/Commands/WhatsAppRemindersCommand.php` | Align fully with unified rule engine |
| `app/Console/Kernel.php` | Keep scheduler aligned with normalized rule schema |

---

## Out of Scope

- ❌ Replacing OpenWA with a different WhatsApp provider
- ❌ Full redesign of non-WhatsApp Tahseel modules
- ❌ Multi-language template authoring system
- ❌ General CRM/customer segmentation platform beyond current filters

---

## Open Questions

1. Should audit data live in the existing message log model/table, or in a separate lightweight audit table?
2. Should dry-run be stored in logs, or only shown live to the operator?
3. Should the calendar stay as a full tab or become a secondary section under automation?
4. Should manual “Send” and Automation eventually share one identical preview component and payload contract?

---

## Implementation Notes

- Existing historical specs cover parts of the story (control center, automation redesign, QR, tab navigation, unified system), but this spec is intended to become the **current umbrella reference** for the next cleanup phase.
- Implementation should proceed in small safe phases:
  1. Spec reconciliation
  2. Rule schema cleanup
  3. Reminder engine cleanup
  4. UI simplification
  5. Safety + audit improvements

---

## Success Criteria

The upgrade is successful when:

1. The user can understand each automation rule without asking what a field does.
2. Preview, manual run, and cron all select the same client/invoice scope.
3. The second rule is a real overdue rule, not a placeholder/custom abstraction.
4. High-impact sends are previewed, testable, and auditable.
5. The automation page becomes easier to maintain without breaking the working calendar/preview flows.
