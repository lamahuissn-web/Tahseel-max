# Tasks: WhatsApp Tab Navigation Bar

**Spec**: [[whatsapp-tab-navigation.md]]
**Plan**: [[whatsapp-tab-navigation.md]]

---

## Task List

### Phase 1: Create Tab Navigation Partial

- [ ] **1.1** Create `_partials/tab-nav.blade.php`
  - Define tabs array: Dashboard, Templates, Send, Automation, Log, Queue
  - Use `request()->routeIs()` for active state
  - Add connection status dot next to Dashboard tab
  - Add auto-refresh JS for connection dot (30s interval)
  - Style: sticky-top, white background, bottom border

### Phase 2: Add Tab Nav to All Pages

- [ ] **2.1** Add `@include('dashbord.whatsapp._partials.tab-nav')` to `dashboard.blade.php`
- [ ] **2.2** Add to `templates.blade.php`
- [ ] **2.3** Add to `send.blade.php`
- [ ] **2.4** Add to `automation.blade.php`
- [ ] **2.5** Add to `log.blade.php`
- [ ] **2.6** Add to `queue.blade.php`

### Phase 3: Style & Polish

- [ ] **3.1** Add CSS for sticky positioning and mobile scroll
- [ ] **3.2** Test on desktop viewport
- [ ] **3.3** Test on mobile viewport

---

## Dependencies

| Task | Depends On |
|------|------------|
| 1.1 | None |
| 2.1 - 2.6 | 1.1 |
| 3.1 | 2.1 - 2.6 |
| 3.2 - 3.3 | 3.1 |

---

## Verification Checklist

- [ ] Tab bar visible on all 6 pages
- [ ] Current page tab is highlighted (green)
- [ ] Tabs navigate correctly
- [ ] Connection dot shows green/red
- [ ] Connection dot auto-refreshes
- [ ] Sticky on scroll
- [ ] Mobile responsive (horizontal scroll)
