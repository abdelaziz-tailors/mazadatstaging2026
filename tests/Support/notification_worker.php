<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';

[$script, $configFile, $directory, $worker, $action, $entityClass, $id, $status] = $argv;
try {
    $config = json_decode(file_get_contents($configFile), true);
    if (($config['host'] ?? null) !== '127.0.0.1' || (int) ($config['port'] ?? 0) !== 33328
        || ($config['database'] ?? null) !== 'mazadat_notifications_test' || ! empty($config['url']) || ! empty($config['unix_socket'])) {
        throw new RuntimeException('Only the dedicated notification test database is allowed.');
    }
    foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'TELESCOPE_ENABLED' => 'false',
        'APP_CONFIG_CACHE' => 'storage/framework/testing/notifications-config.php',
        'APP_ROUTES_CACHE' => 'storage/framework/testing/notifications-routes.php'] as $key => $value) {
        putenv($key.'='.$value); $_ENV[$key] = $_SERVER[$key] = $value;
    }
    $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    \Laravel\Telescope\Telescope::stopRecording();
    config(['database.default' => 'notifications_test', 'database.connections.notifications_test' => $config]);
    \Illuminate\Support\Facades\Queue::fake();
    $db = \Illuminate\Support\Facades\DB::connection();
    $db->statement('SET innodb_lock_wait_timeout = 30');
    $entity = $action === 'status' ? $entityClass::findOrFail($id) : null;
    file_put_contents($directory.'/'.$worker.'.ready', $db->selectOne('SELECT CONNECTION_ID() AS id')->id);
    $deadline = microtime(true) + 10;
    while (! is_file($directory.'/go')) {
        if (microtime(true) > $deadline) { throw new RuntimeException('Worker barrier timeout.'); }
        usleep(10000);
    }
    if ($action === 'status') {
        $entity->update(['status' => $status]);
    } elseif ($action === 'reminder') {
        \Illuminate\Support\Facades\Artisan::call('notifications:upcoming-auction-reminders');
    } elseif ($action === 'push') {
        $fcm = \Mockery::mock(\App\Services\FCMService::class);
        $fcm->shouldReceive('sendToDevice')->andReturnUsing(function () use ($directory) {
            file_put_contents($directory.'/deliveries', "sent\n", FILE_APPEND | LOCK_EX);
            return ['success' => true];
        });
        $app->instance(\App\Services\FCMService::class, $fcm);
        (new \App\Jobs\SendStoredNotification((int) $id))->handle();
    } else {
        throw new RuntimeException('Unsupported worker action.');
    }
    file_put_contents($directory.'/'.$worker.'.done', 'ok');
} catch (Throwable $e) {
    file_put_contents($directory.'/'.$worker.'.error', (string) $e);
    fwrite(STDERR, (string) $e);
    exit(1);
}
