# Tahseel Constitution

## Core Principles

### I. Financial Core is Untouchable
No modifications to the financial core — invoices, payments, balance, subscription management logic. All RADIUS/network features must integrate via hooks, events, or separate controllers without altering billing logic.

### II. Arabic-First UI
The primary interface language is Arabic (RTL). All new views must default to Arabic. Bootstrap Icons for UI elements. Consistent dark theme with gradient headers and cards layout.

### III. Test Before Deploy
Every feature must be verified on CT 131 (192.168.0.83) before any mention of deployment. Use `feature/radius-v3` branch or create a new feature branch. Never commit directly to `live` without testing.

### IV. Keep Existing Patterns
Follow existing Laravel 10 patterns: Controllers in `app/Http/Controllers/Admin/`, views in `resources/views/dashbord/`, services in `app/Services/`. Use Bootstrap 5 + jQuery for frontend. DataTables for tables.

### V. Mobile Responsiveness
All new UI work must be responsive — desktop table view + mobile card view. No horizontal scrolling on phone screens.

## Technology Stack
- **Backend**: Laravel 10, PHP 8.3, MySQL
- **Frontend**: Bootstrap 5, jQuery, DataTables, Bootstrap Icons
- **RADIUS**: FreeRADIUS via SAS4 API (`Sas4ApiService`)
- **Server**: Ubuntu 24.04, Apache, PHP 8.3 FPM

## Spec Workflow
1. Write Spec → 2. Create Plan → 3. Break into Tasks → 4. Implement → 5. Review with Kira → 6. Commit

**Version**: 1.0.0 | **Ratified**: 2026-06-28 | **Last Amended**: 2026-06-28
