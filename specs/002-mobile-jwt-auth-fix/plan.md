# Plan: Mobile JWT Authentication Safety Fix

1. Add a source-level regression test for exception leakage and refresh guard selection.
2. Verify the test fails against current code.
3. Back up `.env` without exposing secrets.
4. Generate `JWT_SECRET` using the package command.
5. Replace `dd()` with a safe API error response and correct the refresh guard.
6. Clear configuration cache.
7. Run syntax checks, targeted tests, JWT generation harness, and safe invalid-login HTTP probe.
8. Report changes and wait for explicit commit instruction.

## Risk

Generating a new JWT secret invalidates previously issued JWTs. JWT generation is currently impossible because no secret exists, making practical token-invalidating risk low.
