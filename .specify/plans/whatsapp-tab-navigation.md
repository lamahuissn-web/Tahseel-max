# Technical Plan: WhatsApp Tab Navigation Bar

**Spec**: [[whatsapp-tab-navigation.md]]
**Created**: 2026-07-15

---

## Overview

Add a sticky tab navigation bar to all 6 WhatsApp Control Center pages. The bar contains clickable tabs with the current page highlighted. A connection status dot auto-refreshes next to the Dashboard tab.

---

## Architecture Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| **Tab style** | Pill buttons (btn-sm) | Consistent with existing Metronic theme |
| **Active state** | `btn-success` (green) | Matches WhatsApp brand color |
| **Sticky behavior** | `sticky-top` CSS class | Native Bootstrap, no JS needed |
| **Mobile layout** | Horizontal scroll with `flex-nowrap` | Keeps tabs visible without dropdown |
| **Connection dot** | Small circle badge | Minimal, non-intrusive |
| **Refresh interval** | 30 seconds | Balance between responsiveness and API calls |

---

## Implementation Steps

### Phase 1: Create Tab Navigation Partial

| Step | Action | Risk |
|------|--------|------|
| 1.1 | Create `resources/views/dashbord/whatsapp/_partials/tab-nav.blade.php` | None |
| 1.2 | Define tabs array with routes, labels, icons | None |
| 1.3 | Add active state detection using `request()->routeIs()` | None |
| 1.4 | Add connection status dot with auto-refresh JS | Low |

### Phase 2: Add Tab Nav to All Pages

| Step | Action | Risk |
|------|--------|------|
| 2.1 | Add `@include('dashbord.whatsapp._partials.tab-nav')` to dashboard | None |
| 2.2 | Add to templates page | None |
| 2.3 | Add to send page | None |
| 2.4 | Add to automation page | None |
| 2.5 | Add to log page | None |
| 2.6 | Add to queue page | None |

### Phase 3: Style & Polish

| Step | Action | Risk |
|------|--------|------|
| 3.1 | Add CSS for sticky positioning and mobile scroll | None |
| 3.3 | Test on mobile viewport | None |

---

## Files to Create/Modify

| File | Action | Description |
|------|--------|-------------|
| `resources/views/dashbord/whatsapp/_partials/tab-nav.blade.php` | CREATE | Tab navigation partial |
| `resources/views/dashbord/whatsapp/dashboard.blade.php` | MODIFY | Include tab-nav |
| `resources/views/dashbord/whatsapp/templates.blade.php` | MODIFY | Include tab-nav |
| `resources/views/dashbord/whatsapp/send.blade.php` | MODIFY | Include tab-nav |
| `resources/views/dashbord/whatsapp/automation.blade.php` | MODIFY | Include tab-nav |
| `resources/views/dashbord/whatsapp/log.blade.php` | MODIFY | Include tab-nav |
| `resources/views/dashbord/whatsapp/queue.blade.php` | MODIFY | Include tab-nav |

---

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Tab nav breaks existing layout | Low | Use `@include` — isolated component |
| Connection API overload | Low | 30s interval, single lightweight endpoint |
| Mobile responsiveness | Low | Use Bootstrap flex utilities |
| CSRF issues | None | No form submissions, just navigation links |

---

## Testing Plan

| Test | Expected Result |
|------|-----------------|
| Open Dashboard | Tab bar visible, Dashboard tab green |
| Click Templates tab | Navigate to Templates page, Templates tab green |
| Click Send tab | Navigate to Send page, Send tab green |
| Check connection dot | Green when connected, red when not |
| Mobile viewport | Tabs scroll horizontally, no wrapping |
| Scroll down | Tab bar stays visible (sticky) |

---

## Estimated Effort

| Phase | Time |
|-------|------|
| Phase 1: Create partial | 10 min |
| Phase 2: Add to all pages | 15 min |
| Phase 3: Style & test | 10 min |
| **Total** | **~35 min** |
