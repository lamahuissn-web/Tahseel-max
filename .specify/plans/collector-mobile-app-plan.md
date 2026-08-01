# Tahseel Collector Mobile App — Implementation Plan

## Summary

Make the existing Laravel collector interface installable as a safe PWA, then package it as an Android Trusted Web Activity (TWA). The server remains the only source of authentication, customer, invoice, and payment behavior. The service worker caches only static app-shell assets and a dedicated offline page; authenticated and financial traffic always goes to the network.

## Technical Approach

1. Correct the existing web manifest for the localized collector entry point.
2. Add properly sized PNG/maskable icons and an offline fallback page.
3. Add a narrowly scoped service worker with network-only behavior for authenticated/dynamic requests.
4. Wire manifest metadata and service-worker registration into `mobile_master.blade.php`.
5. Add a minimal Android TWA project under `mobile/collector-app/`.
6. Build and sign through GitHub Actions using repository secrets; never commit the keystore.
7. Publish Digital Asset Links from `public/.well-known/assetlinks.json`.

## Files to Modify

- `resources/views/dashbord/layouts/head.blade.php` (manifest URL override only)
- `resources/views/dashbord/layouts/mobile_master.blade.php`

## Files to Add

- `public/service-worker.js`
- `public/collector-manifest.json`
- `public/offline.html`
- `public/assets/media/logos/tahseel-collector-192.png`
- `public/assets/media/logos/tahseel-collector-512.png`
- `public/assets/media/logos/tahseel-collector-maskable-512.png`
- `public/.well-known/assetlinks.json`
- `mobile/collector-app/` Android TWA project
- `.github/workflows/build-collector-apk.yml`
- `tests/Feature/CollectorMobileAppTest.php`
- `.specify/specs/collector-mobile-app.md`
- `.specify/plans/collector-mobile-app-plan.md`
- `.specify/tasks/collector-mobile-app-tasks.md`

## Files and Systems Not to Touch

- Invoice/payment/revenue controllers and services
- Database schema or data
- Roles and permissions
- WhatsApp/OpenWA code
- Actual customer VPS

## Implementation Steps

| # | Action | Verification | Risk |
|---|--------|--------------|------|
| 1 | Create feature test for PWA metadata and cache-safety contract | Test fails before implementation | Low |
| 2 | Add manifest, icons, offline page, and service worker | JSON/image/JS validation | Low |
| 3 | Wire metadata and registration into mobile layout | Blade compilation and rendered HTML assertions | Low |
| 4 | Add TWA Android project and release-signing configuration | Gradle release build in CI | Medium |
| 5 | Generate release fingerprint and Digital Asset Links | Fingerprint matches APK signing certificate | Medium |
| 6 | Verify public/mobile behavior on CT 112 development runtime | HTTP, offline, and browser checks | Low |
| 7 | Clean-code guard and one independent review | No blocker/high findings | Low |
| 8 | Commit, push, verify branch and APK artifact | Remote SHA and artifact verified | Low |

## Task Graph

```text
T1 Tests (RED)
   ├──> T2 PWA assets
   └──> T3 Mobile layout wiring
             └──> T4 Render/browser verification

T5 Android/TWA scaffold
   └──> T6 Signing + Digital Asset Links
             └──> T7 GitHub Actions APK build

T4 + T7
   └──> T8 Guard review, commit, push, artifact verification
```

T2/T3 and T5 can proceed independently after the failing contract tests are established.

## Security Notes

- Do not cache requests with non-GET methods.
- Do not cache mobile/admin HTML, JSON, XHR, or authenticated responses.
- Do not implement offline payment submission.
- Store the Android keystore and passwords only in protected local storage/GitHub secrets.
- Use a stable package ID and preserve the signing key for all future upgrades.

## Verification

- `php artisan route:list --name=admin.mobile`
- Focused PHPUnit feature test
- `php artisan view:clear && php artisan view:cache`
- Parse `public/manifest.json`
- Validate PNG dimensions and MIME types
- JavaScript syntax check for service worker
- HTTP checks for manifest, offline page, service worker, and mobile route
- GitHub Actions release APK build
- Inspect APK package/signing certificate and compare with `assetlinks.json`

## Rollback

Revert the feature commit. Because there are no database migrations or financial logic changes, rollback consists only of removing PWA/TWA assets and restoring the previous mobile layout/manifest. Existing browser-based collector access remains functional throughout.
