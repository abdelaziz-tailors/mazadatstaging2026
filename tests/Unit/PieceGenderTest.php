<?php

namespace Tests\Unit;

use App\Http\Requests\api\Video\item\AddPieceRequest;
use App\Http\Requests\api\Video\item\UpdatePieceRequest;
use App\Models\LiveVideoItem;
use App\Services\LiveVideoItemPieceService;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use PHPUnit\Framework\TestCase;

class PieceGenderTest extends TestCase
{
    public function test_gender_validation_for_add_and_update(): void
    {
        $validator = new Factory(new Translator(new ArrayLoader(), 'en'));
        foreach ([new AddPieceRequest(), new UpdatePieceRequest()] as $request) {
            foreach (['male', 'female', 'ذكر', null] as $gender) {
                $this->assertTrue($validator->make(['gender' => $gender], $request->rules())->passes());
            }
            foreach ([12, [], str_repeat('x', 256)] as $gender) {
                $this->assertTrue($validator->make(['gender' => $gender], $request->rules())->fails());
            }
        }
    }

    public function test_migration_and_piece_persistence_preserve_historical_weight(): void
    {
        $container = new Container();
        $db = new Manager($container);
        $db->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $db->bootEloquent();
        $container->instance('db', $db->getDatabaseManager());
        $container->instance('db.schema', $db->getConnection()->getSchemaBuilder());
        Facade::setFacadeApplication($container);
        try {
            $db->getConnection()->getSchemaBuilder()->create('live_video_items', function (Blueprint $table) {
                $table->id();
                $table->integer('quantity')->default(0);
                $table->timestamps();
            });
            $db->getConnection()->getSchemaBuilder()->create('live_video_item_pieces', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('live_video_item_id');
                $table->integer('piece_number');
                $table->double('weight')->nullable();
                foreach (['age', 'identifier', 'baham_count'] as $field) {
                    $table->string($field)->nullable();
                }
                $table->timestamps();
            });
            $db->getConnection()->table('live_video_item_pieces')->insert([
                'live_video_item_id' => 1, 'piece_number' => 1, 'weight' => 80,
            ]);
            $migration = require __DIR__.'/../../database/migrations/2026_11_04_000000_add_gender_to_live_video_item_pieces.php';
            $migration->up();
            $this->assertEquals(80, $db->getConnection()->table('live_video_item_pieces')->value('weight'));
            $this->assertNull($db->getConnection()->table('live_video_item_pieces')->value('gender'));
            $item = LiveVideoItem::create(['quantity' => 1]);
            $piece = LiveVideoItemPieceService::addPiece($item, ['gender' => 'female']);
            $this->assertSame('female', $piece->fresh()->gender);
            $this->assertEquals(2, $item->fresh()->quantity);
            LiveVideoItemPieceService::updatePiece($piece, ['identifier' => 'Updated']);
            $this->assertSame('female', $piece->fresh()->gender);
            LiveVideoItemPieceService::updatePiece($piece, ['gender' => null]);
            $this->assertNull($piece->fresh()->gender);
            $this->assertFalse(LiveVideoItemPieceService::pieceIsEmpty(['gender' => 'male']));
            $this->assertEquals(80, $db->getConnection()->table('live_video_item_pieces')->value('weight'));
        } finally {
            $db->getConnection()->disconnect();
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication(null);
            \Illuminate\Database\Eloquent\Model::unsetConnectionResolver();
        }
    }
}
