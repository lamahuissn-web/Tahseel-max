# Plan: Filter Result Review Modal

**Feature**: Filter Result Review Modal for Send Page
**Spec**: `.specify/specs/whatsapp-send-filter-review.md`
**Created**: 2026-07-12
**Status**: Approved

---

## Approach

### Why a New Modal Instead of Modifying the Existing One?

The filter modal (`#filterModal`) is for **setting filter criteria**. The results modal (`#filterResultsModal`) is for **reviewing and selecting results**. Mixing them would create a confusing two-step flow within one modal. A separate modal keeps each step focused.

### Implementation Strategy

1. **New Modal HTML**: Add `#filterResultsModal` to `send.blade.php` with a DataTable-like structure (but using simple HTML table + JS since we don't need server-side processing).

2. **JS Logic**: 
   - Override the `applyFilters` success handler to store results in a variable and open the new modal
   - Add select-all/individual selection handlers
   - "إضافة المحددين" button adds only checked clients as chips

3. **Data Flow**:
   ```
   Filter Modal → [تطبيق وإضافة] → AJAX (same endpoint) → Store results in JS variable → Open Results Modal
   Results Modal → [إضافة المحددين] → addClient() for each checked row → Close ← [العودة للفلاتر] → Close results, open filters
   ```

### Zero Backend Changes

The current `broadcast()` method with `preview=true` already returns exactly what we need: `{ clients: [{id, name, phone, is_active, unpaid_count}] }`. No controller changes required.

---

## Steps

### Step 1: Add Filter Results Modal HTML
- Add `#filterResultsModal` after `#filterModal` in `send.blade.php`
- Table with columns: checkbox, name, phone, status badge, unpaid count
- Responsive for mobile
- Footer with "العودة للفلاتر" and "إضافة المحددين (N)" buttons

### Step 2: Update JS Filter Handler
- Change `applyFilters` success: store clients in a `let filterResults` variable
- Populate the results modal table
- Show `#filterResultsModal` instead of auto-adding

### Step 3: Add Results Modal JS Logic
- Select-all checkbox handler
- Individual checkbox handler → update count
- "إضافة المحددين" button → iterate checked rows → `addClient()` for each → close modal
- "العودة للفلاتر" button → close results modal → show filter modal

### Step 4: Verify and Test
- Test with various filter combinations
- Verify select-all toggle
- Verify individual selection
- Verify count updates
- Verify adding selected clients as chips
- Verify "العودة للفلاتر" preserves filter state

---

## Estimated Effort

| Step | Complexity | Time |
|------|-----------|------|
| Modal HTML | Low | 10 min |
| JS handler update | Medium | 15 min |
| Results JS logic | Medium | 15 min |
| Verification | Low | 10 min |
| **Total** | | **~50 min** |
