# Tasks: Automation Rules Editor

**Feature**: Automation Rules Editor
**Plan**: `.specify/plans/whatsapp-automation-editor-plan.md`
**Created**: 2026-07-12

---

## Task List

### T1 — Create JSON Config + Migration Logic
**Priority**: P1 | **Status**: Pending | **Deps**: None

- [ ] Check if `whatsapp_automation_rules` exists in `app_config`
- [ ] If not, create it with 4 default rules (reminder, receipt, disconnection, custom)
- [ ] Migrate old `whatsapp_auto_enabled` value to per-rule `enabled`
- [ ] Keep old `whatsapp_remind_before` value as `days_before` for reminder rule

**Files**:
- `app/Http/Controllers/Admin/WhatsAppControlCenterController.php` (add helper method `getAutomationRulesConfig()`)

---

### T2 — Update Controller: automation() method
**Priority**: P1 | **Status**: Pending | **Deps**: T1

- [ ] Rewrite `automation()` to read from JSON config
- [ ] Parse each rule and pass to view with all fields
- [ ] Get template list from `WhatsAppTemplateService::getAll()` for the dropdown
- [ ] Ensure backward compat with old `whatsapp_auto_enabled`

**Files**:
- `app/Http/Controllers/Admin/WhatsAppControlCenterController.php`

---

### T3 — Update Controller: toggle + save methods
**Priority**: P1 | **Status**: Pending | **Deps**: T1

- [ ] Rewrite `toggleAutomationRule($id)` to toggle per-rule `enabled` in JSON
- [ ] Add new `saveAutomationRule(Request $request, $id)`:
  - Accepts: `time`, `days[]`, `template`, `days_before`
  - Validates input
  - Saves to JSON config for that rule
  - Returns updated rule summary HTML or JSON

**Files**:
- `app/Http/Controllers/Admin/WhatsAppControlCenterController.php`
- `routes/admin.php` (verify route exists for save)

---

### T4 — Rewrite automation.blade.php: Card Layout
**Priority**: P1 | **Status**: Pending | **Deps**: T2

- [ ] Replace table with card-based layout
- [ ] Each rule card has:
  - **Header**: Rule name (with icon) + enabled/disabled badge
  - **Body**: Summary line (time, days, template, days-before)
  - **Footer**: Action buttons (تعديل, تشغيل الآن, تفعيل/تعطيل)
- [ ] Keen template design (`.card`, `.card-header`, `.card-body`, `.card-footer`)
- [ ] RTL layout, Arabic UI

**Files**:
- `resources/views/dashbord/whatsapp/automation.blade.php`

---

### T5 — Add Inline Edit Form to Cards
**Priority**: P1 | **Status**: Pending | **Deps**: T4

- [ ] Each card has a hidden `.edit-form` section
- [ ] Click "✏️ تعديل" → toggle `.edit-form` visibility, hide summary
- [ ] Form fields:
  - ⏰ Time picker (`<input type="time">`)
  - 📅 Days of week (7 checkboxes: سبت, أحد, ..., جمعة)
  - 📝 Template dropdown (from `$templates`)
  - 🔢 Number of days before/after (`<input type="number">`)
- [ ] "💾 حفظ" button
- [ ] "✖ إلغاء" button
- [ ] Only one card in edit mode at a time (close others when opening)

**Files**:
- `resources/views/dashbord/whatsapp/automation.blade.php`

---

### T6 — Add JavaScript Logic
**Priority**: P1 | **Status**: Pending | **Deps**: T5

- [ ] Toggle handler: per-rule toggle via AJAX → update badge + button text
- [ ] Edit toggle: expand/collapse edit form, close other open cards
- [ ] Save handler: gather form data → AJAX POST → update card summary → collapse
- [ ] Cancel handler: collapse edit form, restore original values
- [ ] "تشغيل الآن": existing handler, ensure it still works
- [ ] Dynamic day names in summary (e.g. "س, ح, ن" or "كل الأيام")

**Files**:
- `resources/views/dashbord/whatsapp/automation.blade.php` (inline `<script>`)

---

### T7 — Verify & Test
**Priority**: P1 | **Status**: Pending | **Deps**: T6

- [ ] 4 cards display correctly: Reminder, Receipt, Disconnection, Custom
- [ ] Individual toggle works (affects only one rule)
- [ ] Inline edit → change time → save → summary updates
- [ ] Inline edit → change days → save → summary updates
- [ ] Inline edit → cancel → original values restored
- [ ] Only one card in edit mode at a time
- [ ] "تشغيل الآن" still works
- [ ] Page is responsive (cards stack on mobile)
- [ ] Old `whatsapp_auto_enabled` still works (backward compat)
- [ ] View cache cleared, page loads without errors

---

## Rollback Plan

If the feature breaks the automation page:
1. Revert view: `ssh root@192.168.0.83 "cd /var/www/html/tahseel && git checkout -- resources/views/dashbord/whatsapp/automation.blade.php"`
2. Revert controller: `git checkout -- app/Http/Controllers/Admin/WhatsAppControlCenterController.php`
3. Clear cache: `php8.3 artisan view:clear && php8.3 artisan cache:clear`
