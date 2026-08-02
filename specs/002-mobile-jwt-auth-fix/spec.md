# Specification: Mobile JWT Authentication Safety Fix

## Problem

Valid Tahseel Mobile credentials reach JWT token generation, but the production API has no configured JWT signing secret and returns HTTP 500. The login exception handler also exposes internal exception output through `dd()`. The refresh action uses the default session guard instead of the JWT API guard.

## Requirements

1. Valid API authentication can generate a signed JWT.
2. Login exceptions never expose internal exception messages or stack data to clients.
3. JWT refresh explicitly uses the `api` guard.
4. No database, customer, invoice, or payment records are modified during verification.
5. Existing web-admin session authentication remains unchanged.

## Acceptance Criteria

- Production runtime reports a configured JWT secret without printing it.
- A read-only JWT generation harness succeeds for an existing admin.
- The controller contains no `dd()` in login handling.
- Refresh uses `auth('api')->refresh()`.
- Targeted PHPUnit regression test passes.
- An invalid-credential API request returns a safe application response rather than HTTP 500.
