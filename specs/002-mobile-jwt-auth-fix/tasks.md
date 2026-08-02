# Tasks: Mobile JWT Authentication Safety Fix

- [x] Confirm HTTP 500 in live access log.
- [x] Reproduce missing JWT secret without printing credentials.
- [x] Add and run failing regression tests.
- [x] Back up `.env` and generate JWT secret.
- [x] Remove exception leakage from login.
- [x] Correct JWT refresh guard and rebind the rotated token.
- [x] Run focused and live-safe verification.
- [x] Confirm profile and refresh endpoints return HTTP 200 with valid JWTs.
- [x] Document full-suite environment limitation: PHP CLI SQLite driver is absent; 57 unrelated database tests cannot initialize.
- [x] Report risk and changed files.
