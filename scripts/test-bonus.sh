#!/usr/bin/env bash
# Loyalty-bonus test runner (Feature 021).
#
# Why a script: the shared phpunit.xml pins SQLite, and the bonus SQL uses MySQL
# REGEXP / CURDATE, so the suites skip under the default connection. This is the
# one documented command that runs them for real, against a dedicated _test
# database only — never the live one.
#
# Usage:  scripts/test-bonus.sh [extra phpunit args...]
set -uo pipefail

cd "$(dirname "$0")/.." || exit 1

TEST_DB="${TAHSEEL_TEST_DB:-tahseel_secure_payment_test}"

if [[ "$TEST_DB" != *_test ]]; then
  echo "REFUSED: '$TEST_DB' is not a dedicated test database (must contain '_test')." >&2
  exit 2
fi

echo "== bonus suite on $TEST_DB =="
DB_CONNECTION=mysql DB_DATABASE="$TEST_DB" \
  php vendor/bin/phpunit --filter "${TAHSEEL_TEST_FILTER:-Bonus}" "$@"
