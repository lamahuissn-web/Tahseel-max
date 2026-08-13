# Feature 015 implementation evidence

All commands ran in `/root/projects/Tahseel-sas4-gateway-refactor`; no production environment was sourced and no live SAS request/write was executed.

## Vertical RED → GREEN

### Slice 1 — fail-closed gateway
- RED: `./vendor/bin/phpunit tests/Unit/Sas4GatewayTest.php --colors=never`
- Expected failure after test harness directories were created: `Error: Class "App\Services\Sas4\Sas4Gateway" not found` (`1 test, 0 assertions`, exit 2).
- GREEN: same command → `OK (1 test, 2 assertions)`.

### Slice 2 — control write count and verification
- RED: `./vendor/bin/phpunit tests/Unit/Sas4GatewayTest.php --filter=test_enable --colors=never`
- Expected failure: `Undefined array key "verification"` after the single mocked `enableUser` write (`1 test, 1 assertion`, exit 2).
- GREEN: gateway now performs at most one write and a read-only post-write check for reliable fields. `./vendor/bin/phpunit tests/Unit/Sas4GatewayTest.php --colors=never` → `OK (2 tests, 4 assertions)`.

### Migration/status regression GREEN
- `./vendor/bin/phpunit tests/Feature/ClientSasStatusEndpointTest.php tests/Feature/ClientSasStatusBoundsTest.php tests/Feature/ClientSasStatusServiceTest.php tests/Feature/Sas4ApiServiceTimeoutTest.php --colors=never`
- Result: `OK (65 tests, 348 assertions)`.
- Combined focused run before the control verification slice: `OK (66 tests, 350 assertions)`.

## Deterministic checks
- PHP syntax: all modified PHP production/config/test files passed `php -l`.
- Direct transport scan under `app/`: only `Sas4Gateway`, `ClientSasStatusService`, and `Sas4ApiService` itself reference `Sas4ApiService`; migrated controllers/command do not.
- `git diff --check`: passed.
- Migration/real `.env` scan: no changes.
- Active Blade caller scan: online-status now sends JSON `client_ids`, not usernames.
- Secret scan: no added functional SAS credential values; tracked defaults were removed. Matches were only removed lines or the internal cache-key literal.

## Full suite / route-list blockers
- Full `./vendor/bin/phpunit --colors=never`: `267 tests, 634 assertions, 134 errors`. The captured tail shows unrelated WhatsApp fixture/application errors (for example missing `WhatsAppMessageLog` rows); focused SAS suites remain green. No full-suite success is claimed.
- `php artisan route:list --path=sas4` cannot bootstrap in the isolated no-production-env runtime: first missing `APP_URL`, then default MySQL credentials, and under SQLite `no such table: app_config` from `WhatsAppService` construction. Route source registration remains unchanged and focused endpoint tests pass.
- No read-only live SAS check was run because the delegated instruction explicitly said no live SAS requests were needed and prohibited external writes; no credentials were sourced.

## Side effects
- Local isolated worktree edits and dependency installation only.
- No commit, push, merge, deploy, migration, production edit, `.env` edit, or live SAS operation.

## Follow-up audit repair — mobile caller and gateway invariants

### Mobile admin caller RED → GREEN
- RED: `./vendor/bin/phpunit tests/Unit/MobileAdminSasStatusCallerTest.php --colors=never` failed because the active mobile Blade still collected `data-username`, posted form `usernames`, and read rich `enabled`/`online` fields (`1 test, 1 failure`).
- GREEN: same command → `OK (1 test, 11 assertions)`.
- Result: active mobile cards now carry trusted Tahseel client IDs; AJAX posts JSON `{client_ids:[...]}`; labels map `online` → `متصل`, `offline` → `غير متصل`, `unlinked` → `غير مربوط`, `not_found` → `غير موجود`, and `unavailable` → `غير متاح`. Transport/AJAX failure remains truthfully unavailable, not offline.

### Gateway high-risk invariant RED → GREEN
- RED: `./vendor/bin/phpunit tests/Unit/Sas4GatewayTest.php --colors=never` exposed that gateway configuration bypassed `Sas4ApiService::isConfigured()`, missing configuration could lose precedence to `unlinked`, and absent provider usernames could fall back to the requested username. Initial run: `11 tests`, `2 failures`, `8 errors` (the errors were unmet `isConfigured` expectations proving the gateway had not delegated configuration checks).
- GREEN after minimal gateway corrections and one test-fixture correction: same command → `OK (11 tests, 56 assertions)`.
- Proven invariants: statuses delegates once; linked reads normalize unavailable and unlinked without provider reads; missing configuration returns `not_configured` before linkage and prevents read/write transport operations; invalid controls make no provider call; exact identity mismatch/missing identity prevents writes; enable/disable/disconnect/profile each write once maximum; failed writes are not retried or verified; enable/disable/profile mismatch/unavailable returns `verification_failed`; disconnect verification is `not_applicable`; profile+expiration uses the one combined provider write.

### Admin contract/static boundary RED → GREEN
- RED: `./vendor/bin/phpunit tests/Unit/Sas4MigrationBoundaryTest.php --colors=never` → `2 tests`, `1 failure`: admin strict-key validation still used an exact key-array ordering comparison.
- GREEN: same command → `OK (2 tests, 5 assertions)` after changing validation to reject only keys outside `client_ids`, independent of key order.
- The same static boundary test proves migrated `ClientController`, `ClientSasStatusController`, and `Sas4AutoMatch` contain no `Sas4ApiService` reference.

### Final focused regression and deterministic checks
- Focused gateway/SAS run: `./vendor/bin/phpunit tests/Unit/Sas4GatewayTest.php tests/Unit/MobileAdminSasStatusCallerTest.php tests/Unit/Sas4MigrationBoundaryTest.php tests/Feature/ClientSasStatusEndpointTest.php tests/Feature/ClientSasStatusBoundsTest.php tests/Feature/ClientSasStatusServiceTest.php tests/Feature/Sas4ApiServiceTimeoutTest.php tests/Feature/ClientSasUsernameResourceTest.php --colors=never` → `OK (85 tests, 504 assertions)`.
- The first combined run correctly caught eight old feature mocks missing the new transport configuration expectation; test fixtures were updated, then the exact combined command passed.
- `php -l` passed for the gateway, admin controller, both modified Blade files, and every modified/new focused test.
- `git diff --check` passed.
- Active caller scan: both tracked admin status callers post JSON `client_ids`; no active SAS status caller posts `usernames`.
- Direct app transport scan: only `Sas4Gateway`, `ClientSasStatusService`, and the transport class itself reference `Sas4ApiService`; migrated controllers/command do not.
- Work remains uncommitted. No external or live SAS operation was performed.

## Independent-audit blocker repairs

### RED evidence
- Exact identity binding: `Sas4GatewayTest::test_post_write_verification_rejects_a_different_account_with_expected_state` failed because a different account with the expected enabled state returned success (`1 test, 1 failure`).
- Combined deterministic fields: the expiration adversarial filter failed because correct profile plus wrong expiration returned success (`2 tests, 1 failure`).
- Username availability: the provider-response adversarial test failed because provider failure was returned as successful `data=null` (`1 test, 1 failure`).
- Controller ambiguity: the create-flow test with a malformed successful result errored on missing `data`, proving the controller did not explicitly require a proven boolean absence (`1 test, 1 error`).

### GREEN evidence
- Post-write checks now require both the normalized linked username and the pre-write provider ID before checking changed fields.
- Combined profile/expiration checks compare profile ID plus a strictly parsed normalized `Y-m-d` date. Missing, malformed, or mismatched expiration fails `verification_failed`; no extra write or retry was added.
- Username absence now requires a valid list response whose complete rows expose IDs/usernames and contain no exact normalized match. Provider failure returns `unavailable`; malformed/ambiguous responses return `invalid_response`.
- Client create flow now calls `createAccount` only for an explicit `['ok' => true, 'data' => false]` result.
- Exact adversarial run: `6 tests, 25 assertions` passed.
- Gateway/create safety run: `16 tests, 80 assertions` passed.
- Focused Feature 015 run: `90 tests, 525 assertions` passed.
- `php -l` passed for all five touched PHP implementation/test files; `git diff --check` passed; no migration or live `.env` changed; no direct transport bypass was introduced.
- No network, live SAS, schema, commit, push, or deployment side effect occurred.

## Final pagination absence blocker

### RED evidence
- `test_username_absence_is_not_proven_by_a_full_first_page_with_more_provider_pages` reproduced the independent probe (`100` nonmatches, `total=101`, `current_page=1`, `last_page=2`): expected `invalid_response`, actual unsafe proven absence (`1 test, 1 failure`).
- `test_username_absence_fails_closed_for_short_pages_with_malformed_or_contradictory_pagination_metadata` initially returned proven absence for partial metadata (`1 test, 1 failure`).

### GREEN evidence
- `usernameExists()` now proves absence only for a short filtered page with no pagination metadata (the observed SAS response shape), or strict complete metadata where integer `total === returned rows`, `current_page === 1`, and `last_page === 1`.
- Full pages without metadata, metadata indicating later pages, partial/non-integer/malformed metadata, and contradictory totals/pages return `invalid_response`. Exact matches still stop safely as present; no additional provider writes or retries were introduced.
- Gateway suite: `19 tests, 85 assertions` passed.
- Focused Feature 015 suite under SQLite/array/non-routable SAS test values: `94 tests, 533 assertions` passed.
- PHP syntax and `git diff --check` passed. No network, live SAS, schema, `.env`, commit, deploy, or production operation occurred.
