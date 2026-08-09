<?php

/*
 * Executable test database guard.
 *
 * Tests must never run against the production database. PHPUnit applies the
 * <php><env> block before loading this bootstrap, so the effective
 * DB_CONNECTION / DB_DATABASE are visible here. If the configured MySQL
 * database is not one of the dedicated test databases, refuse to boot.
 */

$dbConnection = getenv('DB_CONNECTION') ?: 'sqlite';
$dbDatabase = getenv('DB_DATABASE') ?: ':memory:';

$allowedTestDatabases = [
    'tahseel_secure_payment_test',
    'tahseel_secure_payment_test2',
];

if ($dbConnection === 'mysql' && ! in_array($dbDatabase, $allowedTestDatabases, true)) {
    fwrite(
        STDERR,
        sprintf(
            "FATAL: refusing to run tests against MySQL database \"%s\".\nAllowed dedicated test databases: %s\n",
            $dbDatabase,
            implode(', ', $allowedTestDatabases),
        )
    );
    exit(2);
}

require dirname(__DIR__).'/vendor/autoload.php';

$worktreeLoader = new Composer\Autoload\ClassLoader;
$worktreeLoader->addPsr4('App\\', dirname(__DIR__).'/app');
$worktreeLoader->addPsr4('Tests\\', __DIR__);
$worktreeLoader->register(true);
