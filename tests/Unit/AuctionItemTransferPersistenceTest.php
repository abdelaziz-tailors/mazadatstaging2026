<?php

namespace Tests\Unit;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Services\AuctionItemTransferService;
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
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
        $db = $this->database->getConnection();
        self::assertSame('', $db->selectOne('PRAGMA database_list')->file);
        $db->statement('CREATE TABLE live_videos (id INTEGER PRIMARY KEY, type TEXT, status TEXT, created_at TEXT, updated_at TEXT)');
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
}
