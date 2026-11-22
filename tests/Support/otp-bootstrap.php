<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';

// Refuse an unsafe test configuration before any application boot or schema operations.
foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:',
    'DATABASE_URL' => '', 'DB_URL' => ''] as $key => $expected) {
    if (getenv($key) !== $expected) {
        throw new RuntimeException('OTP tests require isolated testing/sqlite/:memory: configuration.');
    }
}
foreach (['APP_CONFIG_CACHE', 'APP_ROUTES_CACHE'] as $key) {
    $path = getenv($key);
    if (! $path || file_exists(dirname(__DIR__, 2).'/'.$path)) {
        throw new RuntimeException('OTP tests refuse cached application configuration or routes.');
    }
}

$application = require dirname(__DIR__, 2).'/bootstrap/app.php';
$application->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! $application->environment('testing') || config('database.default') !== 'sqlite'
    || config('database.connections.sqlite.database') !== ':memory:'
    || config('database.connections.sqlite.url')) {
    throw new RuntimeException('Effective OTP test database is not isolated.');
}
$connection = Illuminate\Support\Facades\DB::connection();
if ($connection->getDriverName() !== 'sqlite' || $connection->getDatabaseName() !== ':memory:') {
    throw new RuntimeException('OTP tests require an in-memory SQLite connection.');
}
unset($connection, $application);
