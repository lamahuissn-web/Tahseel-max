# Feature Specification: WhatsApp QR Code in Control Center

**Feature Branch**: `feature/whatsapp-control-center`

**Created**: 2026-07-15 | **Updated**: 2026-07-15

**Status**: Draft

**Input**: Discussion with Kira — when the WhatsApp session expires, the admin must go to the OpenWA dashboard (separate app) to scan the QR code. This creates unnecessary friction and context switching.

---

## Problem Statement

When the WhatsApp session expires (QR code expiry, server restart, etc.), the admin currently needs to:

1. Open the OpenWA dashboard (separate URL/app)
2. Find the session
3. Scan the QR code
4. Go back to Tahseel

This is **3 extra steps** and requires knowledge of the OpenWA dashboard.

| Missing | Impact |
|---------|--------|
| 📱 **QR Code in Dashboard** | Admin must leave Tahseel to re-authenticate |
| 🔄 **Auto-refresh on scan** | No feedback when scan succeeds |
| 📊 **Session status visibility** | Can't tell if session needs re-auth from dashboard |

**Goal**: Allow the admin to scan the WhatsApp QR code directly from the Tahseel Control Center dashboard, without leaving the app.

---

## User Stories

### User Story 1 — QR Code Display (Priority: P1)

كمشرف، بدي أشوف **رمز QR** مباشرة من صفحة **WhatsApp Control Center** لما ينتهي الجلسة:
- الصفحة تعرض الرمز تلقائياً لما الجلسة تنقطع
- أقدر أمسح الرمز من واتساب على الجوال
- الرمز يتحدث تلقائياً بعد المسح

**Acceptance Criteria:**
1. QR code section appears when session status is NOT connected
2. QR code is fetched from OpenWA API endpoint
3. QR code displays as image (base64 PNG)
4. "Refresh" button to get new QR code
5. Auto-refresh every 3 seconds to check connection status
6. After successful scan, show success message and reload page
7. QR section is hidden when session is connected

---

### User Story 2 — Connection Status Fix (Priority: P1)

كمشرف، بدي **حالة الاتصال** تعرض بشكل صحيح:
- لما تكون الحالة `qr_ready` (في انتظار المسح)، الصفحة تعرض "غير متصل"
- لما تكون الحالة `connected`، الصفحة تعرض "متصل"

**Acceptance Criteria:**
1. Only `connected` and `ready` statuses are treated as "connected"
2. `qr_ready` and `scan_qr` statuses show as "not connected"
3. QR code section appears for non-connected states

---

## Technical Architecture

### API Endpoints (Existing in OpenWA)

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/sessions/{id}` | GET | Get session status |
| `/api/sessions/{id}/qr` | GET | Get QR code (base64 PNG) |
| `/api/sessions` | POST | Create new session |
| `/api/sessions/{id}/start` | POST | Start session (triggers QR) |

### New Endpoints (Tahseel)

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/admin/whatsapp/qr-code` | GET | Fetch QR from OpenWA |
| `/admin/whatsapp/check-connection` | GET | Check connection status (for polling) |

### Data Flow

```
Dashboard loads
    ↓
Check session status via OpenWA API
    ↓
├── Connected → Show green badge ✅
└── Not Connected → Show QR code section
        ↓
    Fetch QR from OpenWA /api/sessions/{id}/qr
        ↓
    Display QR image (base64 PNG)
        ↓
    User scans with phone
        ↓
    Poll /check-connection every 3 seconds
        ↓
    Connected → Show success banner → Reload page
```

### Files to Modify

| File | Change |
|------|--------|
| `app/Services/WhatsAppService.php` | Fix `qr_ready` status check (DONE) |
| `app/Http/Controllers/Admin/WhatsAppControlCenterController.php` | Add `getQRCode()` and `checkConnection()` methods |
| `routes/admin.php` | Add routes for QR and connection check |
| `resources/views/dashbord/whatsapp/dashboard.blade.php` | Add QR code section + JavaScript |

### Files Already Modified (This Session)

| File | Status |
|------|--------|
| `app/Services/WhatsAppService.php` | ✅ Fixed `qr_ready` status |
| `app/Http/Controllers/Admin/WhatsAppControlCenterController.php` | ✅ Added `getQRCode()` and `checkConnection()` |
| `routes/admin.php` | ✅ Added routes |
| `resources/views/dashbord/whatsapp/dashboard.blade.php` | ✅ Added QR section + JS |

---

## Open Questions

1. **Session creation** — Should the dashboard allow creating a new session if none exists?
2. **QR expiry timeout** — How long is the QR valid? Should we show a countdown?
3. **Multiple sessions** — Should we support multiple OpenWA sessions?

---

## Related Specs

- [[whatsapp-control-center.md]] — Main control center spec
- [[whatsapp-emergency-kill-switch.md]] — Emergency stop feature

---

## Implementation Status

| Component | Status |
|-----------|--------|
| Bug fix (`qr_ready` status) | ✅ Done |
| Controller methods | ✅ Done |
| Routes | ✅ Done |
| Dashboard QR section | ✅ Done |
| JavaScript polling | ✅ Done |
| Testing | 🔄 In Progress |
