# Plan: Automation Rules Editor

**Feature**: Automation Rules Editor
**Spec**: `.specify/specs/whatsapp-automation-editor.md`
**Created**: 2026-07-12
**Status**: Draft

---

## Approach

### Storage Strategy
Use a single JSON blob in `app_config` under key `whatsapp_automation_rules`. This avoids a migration and keeps things simple. Each rule has its own `enabled`, `time`, `days`, `template`, and rule-specific params.

### UI Strategy
Card-based layout with inline editing (expand within card). Only one card can be in edit mode at a time. This keeps the page clean and focused.

### Backend Strategy
Update the existing `automation()` controller method to read from the new JSON config. Add `saveAutomationConfig()` and `toggleAutomationRule()` for per-rule control. Keep backward compatibility with old `whatsapp_auto_enabled`.

---

## Steps

### Step 1: Config Migration
- Read existing `whatsapp_auto_enabled` + old rule values from `app_config`
- Create initial JSON config if not exists
- Seed default 4 rules with sensible defaults

### Step 2: Controller Updates
- Rewrite `automation()` to parse JSON config and pass to view
- Rewrite `toggleAutomationRule()` to toggle per-rule `enabled`
- Add `saveRuleConfig()` to save a single rule's settings
- Keep `runAutomationRule()` as-is (already works)

### Step 3: View — Card Layout
- Rewrite `automation.blade.php`
- Each rule is a `.card` with header (name + status badge), body (summary info), footer (actions)
- Summary shows: time, days, template, days-before
- Actions: تعديل, تشغيل الآن, تفعيل/تعطيل

### Step 4: View — Inline Edit Form
- Click "✏️ تعديل" expands the card with an inline form
- Fields: time picker, day checkboxes (7 days), template dropdown, days-before input
- "💾 حفظ" → AJAX save → collapse + update summary
- "✖ إلغاء" → collapse without saving
- Only one card in edit mode at a time

### Step 5: JavaScript Logic
- Handle toggle (individual rule, not global)
- Handle inline edit expand/collapse
- Handle save via AJAX
- Handle "تشغيل الآن" (existing, just wire up)
- Update card summary after save without page reload

### Step 6: Verify
- Test all 4 rules display
- Test toggle on/off for individual rules
- Test inline edit → save → summary updates
- Test inline edit → cancel → no changes
- Test "تشغيل الآن" still works
- Test only one edit mode at a time
- Test responsive layout

---

## Estimated Effort

| Step | Complexity | Time |
|------|-----------|------|
| Config migration | Low | 10 min |
| Controller updates | Medium | 20 min |
| View — card layout | Medium | 20 min |
| View — inline edit | High | 30 min |
| JS logic | Medium | 25 min |
| Verification | Medium | 15 min |
| **Total** | | **~2 hours** |
