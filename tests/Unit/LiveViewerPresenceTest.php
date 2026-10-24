<?php
namespace Tests\Unit;

use App\Http\Controllers\api\Live\LiveVideoController;
use App\Http\Controllers\FirebaseController;
use App\Models\LiveVideoUser;
use App\Models\VideoView;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade;
use Mockery;
use PHPUnit\Framework\TestCase;

/** No kernel bootstrap, .env, external database, migrations, or Firebase connections. */
class LiveViewerPresenceTest extends TestCase
{
    private $app;
    private $db;
    private $userId = 1;
    private $counts = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Application(dirname(__DIR__, 2));
        $this->app->instance('config', new Repository(['app' => ['locale' => 'en']]));
        $this->app->instance('events', new Dispatcher($this->app));
        $capsule = new Manager($this->app);
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $this->db = $capsule->getConnection();
        $this->app->instance('db', $capsule->getDatabaseManager());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);
        self::assertSame('', $this->db->select('PRAGMA database_list')[0]->file);
        $this->db->statement('CREATE TABLE live_videos (id INTEGER PRIMARY KEY)');
        $this->db->statement('CREATE TABLE live_video_users (id INTEGER PRIMARY KEY AUTOINCREMENT, live_video_id INTEGER, user_id INTEGER, leave INTEGER DEFAULT 0, created_at TEXT, updated_at TEXT)');
        $this->db->statement('CREATE TABLE video_views (id INTEGER PRIMARY KEY AUTOINCREMENT, video_id INTEGER, user_id INTEGER, created_at TEXT, updated_at TEXT)');
        $this->db->table('live_videos')->insert(['id' => 10]);
        $auth = Mockery::mock();
        $auth->shouldReceive('guard')->with('api')->andReturnSelf();
        $auth->shouldReceive('user')->andReturnUsing(function () {
            return $this->userId === null ? null : (object) ['id' => $this->userId];
        });
        $this->app->instance('auth', $auth);
        $response = Mockery::mock(\Illuminate\Contracts\Routing\ResponseFactory::class);
        $response->shouldReceive('json')->andReturnUsing(function ($data, $status = 200) {
            return new JsonResponse($data, $status);
        });
        $this->app->instance(\Illuminate\Contracts\Routing\ResponseFactory::class, $response);
        $localization = Mockery::mock();
        $localization->shouldReceive('getSupportedLocales')->andReturn(['en' => []]);
        \Mcamara\LaravelLocalization\Facades\LaravelLocalization::swap($localization);
        $translator = Mockery::mock(\Illuminate\Contracts\Translation\Translator::class);
        $translator->shouldReceive('get')->andReturnUsing(function ($key) { return $key; });
        $this->app->instance('translator', $translator);
        $firebase = Mockery::mock(FirebaseController::class);
        $firebase->shouldReceive('LiveCount')->andReturnUsing(function ($id, $count) {
            self::assertSame(10, $id);
            self::assertGreaterThanOrEqual(0, $count);
            $this->counts[] = $count;
        });
        $this->app->instance(FirebaseController::class, $firebase);
    }

    protected function tearDown(): void
    {
        $this->db->disconnect();
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        parent::tearDown();
    }

    public function test_join_leave_repeated_leave_and_reconnect_preserve_history(): void
    {
        $controller = new LiveVideoController();
        foreach (['addView', 'addView', 'leaveView', 'leaveView', 'addView', 'leaveView'] as $method) {
            self::assertTrue($controller->$method(10)->getData(true)['success']);
        }
        self::assertSame([1, 1, 0, 0, 1, 0], $this->counts);
        self::assertSame(1, LiveVideoUser::count());
        self::assertSame(1, VideoView::count());
    }

    public function test_two_active_users_and_unknown_leave(): void
    {
        $controller = new LiveVideoController();
        $controller->leaveView(10);
        $controller->addView(10);
        $this->userId = 2;
        $controller->addView(10);
        $this->userId = 1;
        $controller->leaveView(10);
        $controller->leaveView(10);
        self::assertSame([0, 1, 2, 1, 1], $this->counts);
        self::assertSame(2, VideoView::count());
    }

    public function test_disconnect_without_a_client_signal_does_not_fake_cleanup(): void
    {
        $controller = new LiveVideoController();
        $controller->addView(10);
        // No disconnect callback exists in this repository. Rejoin is idempotent.
        self::assertSame(1, LiveVideoUser::where('leave', 0)->count());
        $controller->addView(10);
        self::assertSame([1, 1], $this->counts);
    }

    public function test_historical_view_endpoint_cannot_overwrite_active_count(): void
    {
        $controller = new LiveVideoController();
        $controller->addView(10);
        $controller->leaveView(10);
        $historical = new \App\Http\Controllers\api\Video\VideoController();
        $historical->addView(10, new \App\Http\Requests\api\Video\AddViewVideoRequest());
        self::assertSame([1, 0], $this->counts);
        self::assertSame(1, VideoView::count());
    }

    public function test_duplicate_legacy_rows_are_counted_as_one_user(): void
    {
        LiveVideoUser::create(['live_video_id' => 10, 'user_id' => 1, 'leave' => 0]);
        LiveVideoUser::create(['live_video_id' => 10, 'user_id' => 1, 'leave' => 0]);
        $controller = new LiveVideoController();
        $controller->addView(10);
        $controller->leaveView(10);
        self::assertSame([1, 0], $this->counts);
        self::assertSame(0, LiveVideoUser::where('leave', 0)->count());
    }

    public function test_unauthenticated_and_missing_auction_do_not_write(): void
    {
        $controller = new LiveVideoController();
        self::assertFalse($controller->addView(999)->getData(true)['success']);
        $this->userId = null;
        self::assertFalse($controller->leaveView(10)->getData(true)['success']);
        self::assertSame([], $this->counts);
        self::assertSame(0, LiveVideoUser::count());
    }

    public function test_elapsed_time_and_another_join_do_not_clean_disconnected_presence(): void
    {
        try {
            \Carbon\Carbon::setTestNow('2026-10-24 10:00:00');
            $controller = new LiveVideoController();
            $controller->addView(10);
            \Carbon\Carbon::setTestNow('2026-10-25 10:00:00');
            $this->userId = 2;
            $controller->addView(10);
            self::assertSame([1, 2], $this->counts);
            self::assertSame(2, LiveVideoUser::where('leave', 0)->count());
            self::assertSame(2, VideoView::count());
        } finally {
            \Carbon\Carbon::setTestNow();
        }
    }
}

