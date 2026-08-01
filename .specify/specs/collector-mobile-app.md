# Tahseel Collector Mobile App

**Status:** Approved for implementation

## Problem Statement

Tahseel collectors already use the authenticated mobile interface at `/ar/admin/mobile-view`, and each collector has an individual account. Daily access is slower than necessary because collectors must open a browser and navigate to the site. They want a dedicated Android app icon and an app-like launch experience without duplicating Tahseel's financial logic.

## Goal

Deliver an installable Android collector app that opens the existing Tahseel mobile interface quickly, preserves Laravel authentication, receives web updates without APK rebuilds, and does not duplicate or cache financial operations.

## User Stories

### P1 — Fast app launch

As a collector, I can tap a Tahseel icon and land directly on the mobile collector interface so that I do not navigate through Chrome manually.

**Acceptance criteria**
- Given an authenticated collector, when the app opens, then `/ar/admin/mobile-view` is shown in standalone/full-screen mode.
- Given an unauthenticated or expired session, when the app opens, then the normal localized admin login is shown.
- Given successful login by a non-super-admin collector, then the existing redirect returns the user to the mobile interface.

### P1 — Safe online behavior

As the Tahseel owner, financial pages must always use current server data so that an installed app cannot show stale balances or submit cached payments.

**Acceptance criteria**
- Authenticated HTML, AJAX responses, invoice data, and payment requests are never served from a service-worker cache.
- When the network is unavailable, navigation shows a clear offline page and no payment operation is queued.
- The APK contains no database credentials, API tokens, or collector passwords.

### P1 — Installable Android package

As an operator, I can install a signed APK on a collector phone and update the web interface without redistributing the APK.

**Acceptance criteria**
- A release APK is built from a reproducible GitHub Actions workflow.
- Android package ID is stable.
- The APK opens the HTTPS Tahseel mobile URL.
- Digital Asset Links bind the signed app to the Tahseel domain for Trusted Web Activity verification.

### P2 — Professional mobile identity

As a collector, the app has recognizable Tahseel branding.

**Acceptance criteria**
- PWA metadata includes Tahseel Collector name, portrait orientation, theme colors, and 192/512 pixel icons.
- Android launcher icon and splash/theme colors match the PWA identity.

## Functional Requirements

- FR-001: The web manifest start URL must be `/ar/admin/mobile-view`.
- FR-002: The manifest must use `display: standalone` with a compatible scope.
- FR-003: Mobile pages must link the manifest and register the service worker.
- FR-004: Service-worker navigation must be network-first and may fall back only to a static offline page.
- FR-005: Service worker must not cache authenticated pages, API/AJAX responses, POST requests, or financial data.
- FR-006: Android wrapper must use an HTTPS Trusted Web Activity entry URL.
- FR-007: Signing material must not be committed to Git.
- FR-008: `.well-known/assetlinks.json` must contain the release certificate fingerprint.
- FR-009: Existing collector accounts, Laravel guard, authorization, invoices, and payment logic remain unchanged.
- FR-010: The app must retain ordinary browser/TWA session-cookie behavior and show login when Laravel expires the session.

## Out of Scope

- Native Flutter UI or duplicated REST API
- Offline payment collection or background payment synchronization
- Changes to invoices, revenues, balances, database schema, roles, or collector authorization
- iOS application
- Play Store publication in the first release
- Push notifications

## Edge Cases

- First launch before login
- Expired Laravel session
- No internet during launch
- Network loss while viewing a page
- AJAX search while offline
- Android system back navigation
- External `tel:`, WhatsApp, download, and print links
- Domain verification missing on an environment not yet carrying `assetlinks.json`

## Success Criteria

- SC-001: PWA installability audit finds a valid collector-specific manifest, valid icons, HTTPS start URL behavior, and registered service worker without changing the desktop app manifest.
- SC-002: Direct mobile route returns the expected authenticated redirect behavior without changing existing auth rules.
- SC-003: Offline mode displays the offline page and cannot fabricate or queue a payment.
- SC-004: GitHub Actions produces an installable release APK artifact.
- SC-005: No production financial PHP logic or database migration changes are present in the feature diff.
