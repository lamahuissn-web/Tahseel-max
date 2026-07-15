# Technical Plan: Refactored WhatsApp Automation

**Spec**: [[whatsapp-automation-refactor.md]]
**Created**: 2026-07-15

---

## Overview

Refactor the WhatsApp automation system to be simple and clear. Two rules, simplified filters, preview before every send.

---

## Architecture Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| **Rules** | 2 (Before Disconnection + Overdue) | User's choice |
| **Filters** | Kept but simplified | User wants to filter sometimes |
| **Preview** | Required before every send | User wants to see results first |
| **Calendar** | Kept as-is | User said it's fine |
| **Code structure** | New `ReminderService` class | Centralize all reminder logic |

---

## Files to Create/Modify

| File | Action | Description |
|------|--------|-------------|
| `app/Services/WhatsApp/ReminderService.php` | CREATE | Central reminder logic (preview + send) |
| `resources/views/dashbord/whatsapp/automation.blade.php` | REWRITE | New clean layout |
| `app/Http/Controllers/Admin/WhatsAppControlCenterController.php` | MODIFY | Use ReminderService, add preview endpoint |
| `routes/admin.php` | MODIFY | Add preview route |

---

## Implementation Steps

### Phase 1: Create ReminderService

| Step | Action |
|------|--------|
| 1.1 | Create `ReminderService` with `getBeforeDisconnectionPreview()` |
| 1.2 | Add `getOverduePreview()` |
| 1.3 | Add `sendReminder($ruleId, $clientIds)` |
| 1.4 | Add `getFilteredClients($filters)` |

### Phase 2: Update Controller

| Step | Action |
|------|--------|
| 2.1 | Add `previewAutomationRule($id)` endpoint |
| 2.2 | Simplify `runAutomationRule($id)` to use service |
| 2.3 | Remove duplicate code |

### Phase 3: Rewrite Automation View

| Step | Action |
|------|--------|
| 3.1 | Create new `automation.blade.php` with 2 rule cards |
| 3.2 | Add preview modal |
| 3.3 | Add filter section with clear labels |
| 3.4 | Wire up JavaScript for preview + send |

---

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Breaking existing automation | High | Keep same DB schema, just clean code |
| Preview accuracy | Medium | Use same query logic as send |
| Mobile responsiveness | Low | Use Bootstrap grid |

---

## Estimated Effort

| Phase | Time |
|-------|------|
| Phase 1: Service | 20 min |
| Phase 2: Controller | 15 min |
| Phase 3: View | 30 min |
| **Total** | **~65 min** |
