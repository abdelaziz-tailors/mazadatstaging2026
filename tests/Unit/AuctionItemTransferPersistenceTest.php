<?php

namespace Tests\Unit;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Services\AuctionItemTransferService;
use App\Http\Controllers\api\Live\LiveVideoItemTransferController;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

/** No application bootstrap, .env, migrations, or persistent database. */
class AuctionItemTransferPersistenceTest extends TestCase
{
    private $database;

    protected function setUp(): void
    {
        parent::setUp();
        $app = new Application(dirname(__DIR__, 2));
        $app->instance('config', new Repository());
        $this->database = new Manager($app);
        $this->database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $this->database->setAsGlobal();
        $this->database->bootEloquent();
        $app->instance('db', $this->database->getDatabaseManager());
        $app->instance('validator', new \Illuminate\Validation\Factory(
            new \Illuminate\Translation\Translator(new \Illuminate\Translation\ArrayLoader(), 'en'), $app
        ));
        $app['validator']->setPresenceVerifier(new \Illuminate\Validation\DatabasePresenceVerifier($app['db']));
        $app->instance('translator', new \Illuminate\Translation\Translator(new \Illuminate\Translation\ArrayLoader(), 'en'));
        $app->instance('log', new \Psr\Log\NullLogger());
        $app->instance('laravellocalization', new class {
            public function getSupportedLocales() { return ['en' => []]; }
        });
        $app->instance('auth', new class {
            public function guard($name) { return $this; }
            public function user() { return (object) ['id' => 10]; }
        });
        $app->instance(\Illuminate\Contracts\Routing\ResponseFactory::class, new class {
            public function json($data, $status = 200) { return new \Illuminate\Http\JsonResponse($data, $status); }
        });
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
        $db = $this->database->getConnection();
        self::assertSame('', $db->selectOne('PRAGMA database_list')->file);
        $db->statement('CREATE TABLE live_videos (id INTEGER PRIMARY KEY, user_id INTEGER, type TEXT, status TEXT, created_at TEXT, updated_at TEXT)');
        $db->statement('CREATE TABLE live_video_items (id INTEGER PRIMARY KEY, live_video_id INTEGER, transferred_from_item_id INTEGER, transfer_origin_item_id INTEGER, finished_price REAL, user_finished_id INTEGER, end_at TEXT, winner_video TEXT, status TEXT, created_at TEXT, updated_at TEXT)');
        $db->statement('CREATE TABLE live_video_item_pieces (id INTEGER PRIMARY KEY, live_video_item_id INTEGER, piece_number INTEGER, age TEXT, gender TEXT, identifier TEXT, baham_count INTEGER, created_at TEXT, updated_at TEXT)');
        $db->statement('CREATE TABLE orders (id INTEGER PRIMARY KEY, deleted_at TEXT)');
        $db->statement('CREATE TABLE order_items (id INTEGER PRIMARY KEY, order_id INTEGER, live_video_item_id INTEGER)');
    }

    protected function tearDown(): void
    {
        $this->database->getConnection()->disconnect();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        parent::tearDown();
    }

    public function test_transfer_persists_and_passes_destination_response_lookup_and_rejects_repeat(): void
    {
        $source = LiveVideo::create(['type' => 'live', 'status' => 'end']);
        $target = LiveVideo::create(['type' => 'live', 'status' => 'pending']);
        $item = LiveVideoItem::create(['live_video_id' => $source->id]);
        $item->pieces()->create(['piece_number' => 1, 'identifier' => 'animal']);
        $service = new AuctionItemTransferService();
        $created = $service->transferItems($source, $target, [$item->id]);
        $refreshed = $target->fresh(['video_items']);
        self::assertCount(1, $created);
        self::assertSame($target->id, (int) $created->first()->live_video_id);
        self::assertFalse($created->pluck('id')->diff($refreshed->video_items->pluck('id'))->isNotEmpty());
        self::assertSame('animal', $created->first()->pieces->first()->identifier);
        self::assertCount(0, $source->currentVideoItems);
        self::assertCount(1, $target->currentVideoItems);
        try {
            $service->transferItems($source, $target, [$item->id]);
            self::fail('Repeat transfer must be rejected.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('item_ids', $exception->errors());
        }
        self::assertSame(2, LiveVideoItem::count());
    }

    private function scenario(): array
    {
        $source = LiveVideo::create(['user_id' => 10, 'type' => 'live', 'status' => 'end']);
        $target = LiveVideo::create(['user_id' => 10, 'type' => 'live', 'status' => 'pending']);
        $item = LiveVideoItem::create(['live_video_id' => $source->id]);
        $item->pieces()->create(['piece_number' => 1, 'identifier' => 'animal']);
        $request = new class extends \Illuminate\Http\Request {
            public function validate(array $rules) { return app('validator')->validate($this->all(), $rules); }
        };
        $request->replace(['item_id' => $item->id, 'target_auction_id' => $target->id, 'transfer_mode' => 'existing']);
        return [$source, $target, $item, $request];
    }

    private function controller(bool $failResponse = false): LiveVideoItemTransferController
    {
        // Inspect the controller's resource inputs without unrelated auction
        // resource queries. Response-construction failures must also roll back.
        return new class($failResponse) extends LiveVideoItemTransferController {
            private $failResponse;
            public function __construct($failResponse) { $this->failResponse = $failResponse; }
            public function success_response($message = null, $data = []) {
                if ($this->failResponse) {
                    throw new \RuntimeException('Injected response construction failure');
                }
                return response()->json(['success' => true, 'code' => 200, 'message' => $message, 'data' => [
                    'target_auction' => ['id' => $data['target_auction']->resource->id],
                    'transferred_items' => $data['transferred_items']->collection->map(function ($item) {
                        return ['id' => $item->id];
                    })->all(),
                ]]);
            }
        };
    }

    public function test_controller_returns_new_destination_ids_and_rejects_duplicate(): void
    {
        [$source, $target, $item, $request] = $this->scenario();
        $controller = $this->controller();
        $response = $controller->transfer($request, new AuctionItemTransferService());
        self::assertSame(200, $response->getStatusCode());
        $body = $response->getData(true);
        self::assertTrue($body['success']);
        $copyId = $body['data']['transferred_items'][0]['id'];
        self::assertNotSame($item->id, $copyId);
        self::assertSame($target->id, $body['data']['target_auction']['id']);
        self::assertTrue($target->video_items()->whereKey($copyId)->exists());
        self::assertCount(0, $source->currentVideoItems);
        self::assertSame(422, $controller->transfer($request, new AuctionItemTransferService())->getStatusCode());
        self::assertSame(2, LiveVideoItem::count());
        self::assertSame(0, $this->database->getConnection()->transactionLevel());
    }

    public function test_destination_verification_failure_rolls_back_items_and_pieces(): void
    {
        [$source, $target, $item, $request] = $this->scenario();
        $second = LiveVideoItem::create(['live_video_id' => $source->id]);
        $second->pieces()->create(['piece_number' => 1, 'identifier' => 'second']);
        $request->merge(['item_ids' => [$item->id, $second->id]]);
        $service = new class extends AuctionItemTransferService {
            public function transferItems(LiveVideo $source, LiveVideo $target, array $itemIds): \Illuminate\Database\Eloquent\Collection {
                $created = parent::transferItems($source, $target, $itemIds);
                // Fault injection: persisted copies exist but the returned IDs
                // do not match the destination lookup.
                $created->first()->id = $itemIds[0];
                return $created;
            }
        };
        $response = $this->controller()->transfer($request, $service);
        self::assertSame(422, $response->getStatusCode());
        self::assertFalse($response->getData(true)['success']);
        self::assertSame('The transferred item was not returned by the selected destination auction.', $response->getData(true)['message']);
        self::assertCount(0, $target->video_items);
        self::assertCount(2, $source->currentVideoItems);
        self::assertSame(2, LiveVideoItem::count());
        self::assertSame(2, \App\Models\LiveVideoItemPiece::count());
        self::assertSame(0, $this->database->getConnection()->transactionLevel());
    }

    public function test_response_construction_failure_rolls_back_transfer(): void
    {
        [$source, $target, $item, $request] = $this->scenario();
        try {
            $this->controller(true)->transfer($request, new AuctionItemTransferService());
            self::fail('Expected response failure.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Injected response construction failure', $exception->getMessage());
        }
        self::assertSame(1, LiveVideoItem::count());
        self::assertSame(1, \App\Models\LiveVideoItemPiece::count());
        self::assertCount(0, $target->video_items);
        self::assertCount(1, $source->currentVideoItems);
    }

    public function test_controller_preserves_ownership_and_transfer_restrictions(): void
    {
        [$source, $target, $item, $request] = $this->scenario();
        $db = $this->database->getConnection();
        foreach (['owner', 'status', 'type', 'sold'] as $restriction) {
            $db->table('live_videos')->where('id', $target->id)->update([
                'user_id' => $restriction === 'owner' ? 20 : 10,
                'type' => $restriction === 'type' ? 'recorded' : 'live',
            ]);
            $db->table('live_videos')->where('id', $source->id)->update(['status' => $restriction === 'status' ? 'start' : 'end']);
            $db->table('live_video_items')->where('id', $item->id)->update(['finished_price' => $restriction === 'sold' ? 100 : null]);
            $response = $this->controller()->transfer($request, new AuctionItemTransferService());
            self::assertSame($restriction === 'owner' ? 404 : 422, $response->getStatusCode());
            self::assertFalse($response->getData(true)['success']);
            self::assertSame(1, LiveVideoItem::count());
            self::assertSame(0, $target->video_items()->count());
        }
    }
}
