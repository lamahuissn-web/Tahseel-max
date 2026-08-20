# Feature 001: WhatsApp Driver Selector

## Overview
Allow the administrator to manually switch between Zernio (Meta Cloud API) and OpenWA (self-hosted) WhatsApp transports from the WhatsApp Control Center dashboard.

## Risk Level
**Low** — UI toggle that writes to `.env` file. No customer-facing impact. No financial or PII risk.

## User Story
As the ISP administrator, I want to switch WhatsApp transport providers from the dashboard so that if one provider has issues, I can quickly switch to the other without touching the server.

## Requirements

### Functional
1. **Driver Display**: Show current active driver (Zernio or OpenWA) with badge and description
2. **One-Click Switch**: Button to toggle between drivers
3. **Confirmation Dialog**: Warn user before switching
4. **Immediate Effect**: Switch takes effect without server restart
5. **Persistence**: Choice persists across page refreshes (stored in `.env`)

### Non-Functional
1. **No downtime**: Switch completes in <1 second
2. **No data loss**: Switch does not affect message logs or queue
3. **Reversible**: Can switch back immediately
4. **Secure**: Only admin can switch (authenticated route)

## Acceptance Criteria
- [x] Dashboard shows current driver with badge
- [x] Clicking switch button toggles driver
- [x] Confirmation dialog appears before switch
- [x] `.env` file updated with new driver
- [x] Config cache cleared after switch
- [x] Page refreshes with new driver active
- [x] Invalid driver values rejected
- [x] No duplicate WHATSAPP_DRIVER lines in `.env`

## Edge Cases
- **Invalid driver value**: Rejected with error message
- **File permission error**: `.env` must be writable by www-data
- **Concurrent switches**: Last write wins (acceptable for single admin)
- **Missing .env line**: New line appended

## Out of Scope
- Auto-fallback between drivers
- Driver health monitoring
- Per-message-type driver selection
- Multiple admin roles

## Test Evidence
| Test | Status |
|---|---|
| Initial state (zernio) | ✅ |
| Switch to openwa | ✅ |
| Switch back to zernio | ✅ |
| WhatsAppService detects driver | ✅ |
| Invalid driver values rejected | ✅ |
| .env file integrity | ✅ |
| Config cache clear | ✅ |
| View renders driver card | ✅ |
| Final state verification | ✅ |
| WhatsAppService dispatch logic | ✅ |

## Files Changed
- `resources/views/dashbord/whatsapp/dashboard.blade.php` — Added driver selector card
- `.env` — WHATSAPP_DRIVER toggle (runtime)

## Commits
- None yet (uncommitted changes on `spike/zernio-whatsapp-adapter` branch)

## Status
**IMPLEMENTED & TESTED** — Ready for commit.
