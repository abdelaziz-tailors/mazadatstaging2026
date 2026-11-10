<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';

[$script, $configFile, $directory, $worker, $action, $id, $token] = $argv;
try {
    $config = json_decode(file_get_contents($configFile), true);
    if (($config['host'] ?? null) !== '127.0.0.1' || (int) ($config['port'] ?? 0) !== 33328
        || ($config['database'] ?? null) !== 'mazadat_notifications_test' || ! empty($config['url']) || ! empty($config['unix_socket'])) {
        throw new RuntimeException('Only the disposable notification database is allowed.');
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
    $db->statement('SET innodb_lock_wait_timeout = 20');
    $wait = function (string $file) {
        $deadline = microtime(true) + 30;
        while (! is_file($file)) {
            if (microtime(true) > $deadline) { throw new RuntimeException('Worker barrier timeout: '.$file); }
            usleep(10000);
        }
    };
    file_put_contents($directory.'/'.$worker.'.ready', $db->selectOne('SELECT CONNECTION_ID() AS id')->id);
    $wait($directory.'/'.$worker.'.go');
    if ($action === 'push') {
        $fcm = \Mockery::mock(\App\Services\FCMService::class);
        $fcm->shouldReceive('sendToDevice')->andReturnUsing(function ($token, $title, $body, $data) use ($directory, $worker, $wait) {
            file_put_contents($directory.'/'.$worker.'.payload', json_encode(compact('token', 'title', 'body', 'data')));
            if (is_file($directory.'/'.$worker.'.pause')) { $wait($directory.'/'.$worker.'.release'); }
            return ['success' => true];
        });
        $app->instance(\App\Services\FCMService::class, $fcm);
        (new \App\Jobs\SendStoredNotification((int) $id))->handle();
    } elseif ($action === 'register') {
        $app->make(\App\Services\PushTokenOwnership::class)->register((int) $id, $token);
    } else {
        $db->transaction(function () use ($action, $app, $id, $token, $directory, $worker, $wait) {
            $app->make(\App\Services\PushTokenOwnership::class)->register((int) $id, $token);
            if ($action === 'register-hold') {
                touch($directory.'/'.$worker.'.holding');
                $wait($directory.'/'.$worker.'.release');
            }
        }, 3);
    }
    touch($directory.'/'.$worker.'.done');
} catch (Throwable $e) {
    file_put_contents($directory.'/'.$worker.'.error', (string) $e);
    fwrite(STDERR, (string) $e);
    exit(1);
}
