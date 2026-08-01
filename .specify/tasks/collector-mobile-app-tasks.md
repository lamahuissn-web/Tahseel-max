# Tahseel Collector Mobile App — Tasks

- [ ] T1 Write feature tests for manifest, mobile layout metadata, offline fallback, service-worker cache safety, and Digital Asset Links.
- [ ] T2 Run T1 and confirm it fails because the collector PWA contract is not implemented.
- [ ] T3 Generate Tahseel Collector icons in required PWA dimensions.
- [ ] T4 Replace the generic manifest with collector-specific install metadata and localized start URL.
- [ ] T5 Add static offline page and a service worker that never caches authenticated/financial traffic.
- [ ] T6 Link the manifest, colors, icons, and service-worker registration from the mobile master layout.
- [ ] T7 Run focused tests and make them pass.
- [ ] T8 Add minimal Android Trusted Web Activity project with stable package ID.
- [ ] T9 Add release signing through environment variables/GitHub secrets; ensure no key material is tracked.
- [ ] T10 Generate release certificate fingerprint and `public/.well-known/assetlinks.json`.
- [ ] T11 Add GitHub Actions workflow that builds and uploads the signed release APK.
- [ ] T12 Compile Laravel views, validate JSON/JS/images, and run HTTP/browser checks.
- [ ] T13 Run clean-code guard and one independent review; fix blockers/high findings.
- [ ] T14 Commit intended files, push branch, verify remote SHA and downloadable APK artifact.
