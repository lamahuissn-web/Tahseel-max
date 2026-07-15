# Feature Specification: WhatsApp Control Center Tab Navigation

**Feature Branch**: `feature/whatsapp-control-center`

**Created**: 2026-07-15

**Status**: Draft

**Input**: Each WhatsApp tab (Dashboard, Templates, Send, Automation, Log, Queue) is a separate page. To switch between tabs, the user must go back to the Dashboard or use the sidebar. This creates unnecessary clicks and context switching.

---

## Problem Statement

Currently, the WhatsApp Control Center has 6 separate pages:

| Page | Route |
|------|-------|
| Dashboard | `admin.whatsapp.dashboard` |
| Templates | `admin.whatsapp.templates` |
| Send | `admin.whatsapp.send` |
| Automation | `admin.whatsapp.automation` |
| Log | `admin.whatsapp.log` |
| Queue | `admin.whatsapp.queue` |

| Missing | Impact |
|---------|--------|
| 📑 **Tab navigation bar** | User must go back to Dashboard to switch tabs |
| 🔴 **Connection indicator** | No visual cue about WhatsApp connection status |
| 🔍 **Log search** | Can't search by client name/phone in log |

**Goal**: Add a sticky tab navigation bar at the top of every WhatsApp page, so the user can switch between tabs with one click.

---

## User Stories

### User Story 1 — Tab Navigation Bar (Priority: P1)

كمشرف، بدي أقدر **أتنقل بين التبويبات** بسرعة من أي صفحة في WhatsApp Control Center:
- شريط تنقل ثابت في أعلى الصفحة
- التبويب الحالي يكون محدد بلون مختلف
- أقدر أضغط على أي تبويب للانتقال مباشرة

**Acceptance Criteria:**
1. Tab navigation bar appears at the top of all 6 WhatsApp pages
2. Current page tab is highlighted (active state)
3. Tabs are clickable and navigate to the correct page
4. Bar is sticky (stays visible when scrolling)
5. Works on mobile (responsive)
6. Connection status indicator (green/red dot) next to Dashboard tab

---

### User Story 2 — Connection Status Indicator (Priority: P2)

كمشرف، بدي أشوف **حالة الاتصال** مباشرة من شريط التنقل:
- نقطة خضراء = متصل ✅
- نقطة حمراء = غير متصل ❌
- التحديث تلقائي (كل 30 ثانية)

**Acceptance Criteria:**
1. Green dot appears next to Dashboard tab when connected
2. Red dot appears when not connected
3. Status auto-refreshes every 30 seconds
4. Clicking the dot shows a tooltip with details

---

## Technical Architecture

### Files to Modify

| File | Change |
|------|--------|
| `resources/views/dashbord/whatsapp/dashboard.blade.php` | Add tab nav bar |
| `resources/views/dashbord/whatsapp/templates.blade.php` | Add tab nav bar |
| `resources/views/dashbord/whatsapp/send.blade.php` | Add tab nav bar |
| `resources/views/dashbord/whatsapp/automation.blade.php` | Add tab nav bar |
| `resources/views/dashbord/whatsapp/log.blade.php` | Add tab nav bar |
| `resources/views/dashbord/whatsapp/queue.blade.php` | Add tab nav bar |

### Tab Navigation Structure

```html
<div class="whatsapp-tab-nav sticky-top bg-white border-bottom px-4 py-2">
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.whatsapp.dashboard') }}"
           class="btn btn-sm {{ request()->routeIs('admin.whatsapp.dashboard') ? 'btn-success' : 'btn-light' }}">
            📊 Dashboard
            <span class="connection-dot" id="connection-dot"></span>
        </a>
        <a href="{{ route('admin.whatsapp.templates') }}"
           class="btn btn-sm {{ request()->routeIs('admin.whatsapp.templates') ? 'btn-success' : 'btn-light' }}">
            📝 Templates
        </a>
        <!-- ... more tabs ... -->
    </div>
</div>
```

### Connection Status API

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/admin/whatsapp/check-connection` | GET | Already exists ✅ |

### JavaScript for Connection Dot

```javascript
function updateConnectionDot() {
    $.get(checkConnectionUrl, function(res) {
        var $dot = $('#connection-dot');
        if (res.connected) {
            $dot.removeClass('bg-danger').addClass('bg-success');
        } else {
            $dot.removeClass('bg-success').addClass('bg-danger');
        }
    });
}
setInterval(updateConnectionDot, 30000);
```

---

## Open Questions

1. **Tab style** — Pill buttons or underline style?
2. **Mobile layout** — Horizontal scroll or dropdown?
3. **Quick actions** — Keep the Quick Actions card on Dashboard, or move some to the tab bar?

---

## Related Specs

- [[whatsapp-control-center.md]] — Main control center spec
- [[whatsapp-qr-code.md]] — QR code feature spec

---

## Implementation Status

| Component | Status |
|-----------|--------|
| Spec created | ✅ Done |
| Plan created | 🔄 Pending |
| Tasks broken down | 🔄 Pending |
| Implementation | 🔄 Pending |
| Testing | 🔄 Pending |
