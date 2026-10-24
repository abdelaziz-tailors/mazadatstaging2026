<?php
require dirname(__DIR__, 2).'/vendor/autoload.php';
require __DIR__.'/live_viewer_fixture.php';
[$script, $configPath, $directory, $worker, $userId, $action, $mode] = $argv;
try {
    if ($action === 'delayed-write') {
        viewerWait($directory.'/'.$worker.'.release');
        viewerPublish($directory, $worker, (int) $userId);
    } else {
        [$app, $db] = viewerFixture(json_decode(file_get_contents($configPath), true), (int) $userId,
            function ($id, $count) use ($directory, $worker, $mode) {
                file_put_contents($directory.'/'.$worker.'.publish', (string) $count);
                if ($mode === 'pause-publish') { viewerWait($directory.'/'.$worker.'.release'); }
                if ($mode === 'timeout') {
                    throw new RuntimeException('Simulated ambiguous transport timeout; remote write may still apply.');
                }
                viewerPublish($directory, $worker, $count);
                if ($mode === 'pause-after-publish') {
                    file_put_contents($directory.'/'.$worker.'.published', (string) $count);
                    viewerWait($directory.'/'.$worker.'.release');
                }
            });
        if ($mode === 'pause-history') {
            $db->listen(function ($query) use ($directory, $worker) {
                if (stripos($query->sql, 'select') === 0 && strpos($query->sql, '`video_views`') !== false) {
                    file_put_contents($directory.'/'.$worker.'.selected', 'ready');
                    viewerWait($directory.'/'.$worker.'.release');
                }
            });
        }
        file_put_contents($directory.'/'.$worker.'.started', (string) $db->selectOne('SELECT CONNECTION_ID() AS id')->id);
        if ($action === 'history') {
            $response = (new App\Http\Controllers\api\Video\VideoController())->addView(10, new App\Http\Requests\api\Video\AddViewVideoRequest());
        } else {
            $response = (new App\Http\Controllers\api\Live\LiveVideoController())->$action(10);
        }
        file_put_contents($directory.'/'.$worker.'.response', json_encode($response->getData(true)));
        $db->disconnect();
        Mockery::close();
    }
    file_put_contents($directory.'/'.$worker.'.done', 'ok');
} catch (Throwable $e) {
    file_put_contents($directory.'/'.$worker.'.error', (string) $e);
    fwrite(STDERR, (string) $e);
    exit(1);
}
