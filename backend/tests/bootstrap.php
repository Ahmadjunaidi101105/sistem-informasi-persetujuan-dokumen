<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Environment Overrides
|--------------------------------------------------------------------------
|
| docker-compose.yml injects DB_CONNECTION, DB_DATABASE, CACHE_STORE and
| QUEUE_CONNECTION into the container as real environment variables. Those
| land in $_SERVER, and Laravel's env repository reads $_SERVER *before*
| $_ENV/getenv() — while PHPUnit's <env> entries only write $_ENV/putenv().
|
| The result is that phpunit.xml alone cannot redirect the test suite away
| from the live database: RefreshDatabase would run against `sipdok` and
| wipe all seeded development data.
|
| Overriding all three superglobals here — before the framework boots — is
| what actually guarantees the suite is isolated on `sipdok_testing`.
|
*/

$overrides = [
    'APP_ENV' => 'testing',
    'DB_CONNECTION' => 'pgsql',
    'DB_DATABASE' => 'sipdok_testing',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'SESSION_DRIVER' => 'array',
    'MAIL_MAILER' => 'array',
    'BCRYPT_ROUNDS' => '4',
];

foreach ($overrides as $key => $value) {
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv("{$key}={$value}");
}

/*
| A cached config file (php artisan config:cache) bakes in the *live* database
| name and would silently defeat every override above — putting RefreshDatabase
| back on `sipdok`. Drop it so the test run always resolves config from env.
*/
$cachedConfig = __DIR__ . '/../bootstrap/cache/config.php';
if (file_exists($cachedConfig)) {
    @unlink($cachedConfig);
}

require __DIR__ . '/../vendor/autoload.php';
