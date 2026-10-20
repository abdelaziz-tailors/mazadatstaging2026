<?php

namespace Tests\Feature\Api;

use App\Http\Requests\api\Video\AddVideoRequest;
use App\Http\Requests\api\Video\item\StoreItemRequest;
use App\Http\Requests\api\Video\item\AddPieceRequest;
use App\Http\Requests\api\Video\item\UpdatePieceRequest;
use App\Http\Requests\api\Video\item\updateItemRequest;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuctionCreationValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'app.debug' => false]);
        DB::purge('sqlite');
        $this->withoutMiddleware('APISettings');
        app()->setLocale('en');
        $user = (new User(['user_type' => 'vendor']))->forceFill(['id' => 1]);
        $user->setRelation('admin', new Admin(['partner' => 'subscriber']));
        Passport::actingAs($user, [], 'api');
        Schema::create('validation_writes', function (Blueprint $table) {
            $table->id();
            $table->string('kind');
        });
        // Exercise the real FormRequest lifecycle with an isolated persistence probe.
        // Valid requests reaching this probe do not exercise external auction services.
        Route::post('/validation/auction', function (AddVideoRequest $request) {
            DB::table('validation_writes')->insert(['kind' => 'auction']);
            return response()->json(['success' => true]);
        });
        Route::post('/validation/animal', function (StoreItemRequest $request) {
            DB::table('validation_writes')->insert(['kind' => 'animal']);
            return response()->json(['success' => true]);
        });
        Route::post('/validation/head', function (AddPieceRequest $request) {
            DB::table('validation_writes')->insert(['kind' => 'head']);
            return response()->json(['success' => true]);
        });
        Route::post('/validation/head-update', function (UpdatePieceRequest $request) { return ['success' => true]; });
        Route::post('/validation/animal-update', function (updateItemRequest $request) { return ['success' => true]; });
    }

    private function auction(): array
    {
        return ['title_ar' => 'Auction', 'start_price' => 0, 'information_ar' => 'Details',
            'date_start_at' => '2026-11-01', 'date_end_at' => '2026-11-02',
            'time_start_at' => '10:00', 'time_end_at' => '11:00',
            'commission_amount' => 2.5, 'commission_payer' => 'seller'];
    }

    public function test_commission_validation_and_legacy_response_contract(): void
    {
        foreach (['commission_amount', 'commission_payer'] as $field) {
            $data = $this->auction();
            unset($data[$field]);
            $this->postJson('/api/live/add', $data)->assertOk()->assertJsonPath('success', false)
                ->assertJsonStructure(['errors' => [$field]]);
        }
        foreach ([['commission_amount' => -1], ['commission_amount' => 'abc'], ['commission_amount' => ''],
            ['commission_payer' => 'Seller'], ['commission_payer' => '']] as $invalid) {
            $this->postJson('/validation/auction', array_replace($this->auction(), $invalid))
                ->assertOk()->assertJsonPath('success', false);
        }
        $this->assertDatabaseCount('validation_writes', 0);
        foreach (['seller', 'buyer'] as $payer) {
            $this->postJson('/validation/auction', array_replace($this->auction(), ['commission_payer' => $payer]))
                ->assertOk()->assertJsonPath('success', true);
        }
        $this->assertDatabaseCount('validation_writes', 2);
    }

    public function test_images_are_required_for_every_animal_before_any_write(): void
    {
        $data = ['lineage_title_ar' => ['Animal'], 'weight' => [12.5]];
        foreach ([[], ['image' => []], ['image' => 'bad'], ['image' => [[]]],
            ['image' => [[UploadedFile::fake()->create('bad.pdf', 10, 'application/pdf')]]],
            ['image' => [[UploadedFile::fake()->image('big.jpg')->size(20001)]]],
            ['image' => [[new UploadedFile('', 'failed.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true)]]]] as $invalid) {
            $this->post('/api/live/items/add/1', array_merge($data, $invalid))
                ->assertStatus(422)->assertJsonPath('success', false);
        }
        $this->post('/validation/animal', ['lineage_title_ar' => ['One', 'Two'], 'weight' => [10, 20],
            'image' => [[UploadedFile::fake()->image('one.jpg')]]])->assertStatus(422);
        $this->assertDatabaseCount('validation_writes', 0);
        $this->post('/validation/animal', $data + ['image' => [[UploadedFile::fake()->image('ok.jpg')]]])
            ->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('validation_writes', 1);
    }

    public function test_all_head_creation_shapes_reject_invalid_weights(): void
    {
        foreach ([0, -1, '', null, 'abc'] as $weight) {
            $this->postJson('/api/live/items/pieces/add/1', ['weight' => $weight])->assertStatus(422);
            $this->postJson('/validation/head', ['weight' => $weight])->assertStatus(422);
            $this->post('/validation/animal', ['lineage_title_ar' => ['Animal'], 'weight' => [$weight],
                'image' => [[UploadedFile::fake()->image('ok.jpg')]]])->assertStatus(422);
            $this->post('/validation/animal', ['lineage_title_ar' => ['Animal'], 'quantity' => [2],
                'pieces' => [[['weight' => 12], ['weight' => $weight]]],
                'image' => [[UploadedFile::fake()->image('ok.jpg')]]])->assertStatus(422);
            $this->postJson('/validation/head-update', ['weight' => $weight])->assertStatus(422);
            $this->postJson('/validation/animal-update', ['pieces' => [['weight' => $weight]]])->assertStatus(422);
        }
        $this->postJson('/validation/head', [])->assertStatus(422);
        $this->post('/validation/animal', ['lineage_title_ar' => ['Animal'],
            'image' => [[UploadedFile::fake()->image('ok.jpg')]]])->assertStatus(422);
        $this->assertDatabaseCount('validation_writes', 0);
        $this->postJson('/validation/head', ['weight' => 12.75])->assertOk()->assertJsonPath('success', true);
        $this->post('/validation/animal', ['lineage_title_ar' => ['Animal'], 'quantity' => [2],
            'pieces' => [[['weight' => 12.5], ['weight' => 20]]],
            'image' => [[UploadedFile::fake()->image('ok.png')]]])->assertOk()->assertJsonPath('success', true);
        $this->postJson('/validation/animal-update', ['information' => 'Updated'])->assertOk();
        $this->postJson('/validation/head-update', ['identifier' => 'Updated'])->assertOk();
        $this->assertDatabaseCount('validation_writes', 2);
    }

    public function test_nested_heads_allow_unused_flat_weight_but_require_every_nested_weight(): void
    {
        $data = ['lineage_title_ar' => ['Animal'], 'quantity' => [2], 'weight' => [null],
            'pieces' => [[['weight' => 12.5], ['weight' => 20]]]];
        $this->post('/validation/animal', $data + ['image' => [[UploadedFile::fake()->image('ok.jpg')]]])
            ->assertOk()->assertJsonPath('success', true);
        unset($data['pieces'][0][1]['weight']);
        $this->post('/validation/animal', $data + ['image' => [[UploadedFile::fake()->image('ok.jpg')]]])
            ->assertStatus(422)->assertJsonStructure(['errors' => ['pieces.0.1.weight']]);
        $this->postJson('/validation/animal-update', ['age' => 2])->assertStatus(422)
            ->assertJsonStructure(['errors' => ['weight']]);
        $this->assertDatabaseCount('validation_writes', 1);
    }

    public function test_real_creation_endpoints_persist_valid_requests_and_reject_invalid_batches(): void
    {
        \Mockery::mock('overload:App\\Http\\Controllers\\FirebaseController')
            ->shouldReceive('create', 'create_item')->andReturnNull();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fcm_token')->nullable();
            $table->string('app_lang')->nullable();
            $table->softDeletes();
        });
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('status');
            $table->dateTime('expires_at');
            $table->integer('remaining_auctions');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('live_videos', function (Blueprint $table) {
            $table->id();
            foreach (['user_id', 'admin_id', 'partner_id', 'title_ar', 'start_price', 'date_start_at',
                'date_end_at', 'time_start_at', 'time_end_at', 'status', 'image', 'information_ar',
                'terms_conditions_ar', 'city_id', 'type', 'partners_type', 'commission_amount',
                'commission_payer', 'service_fee', 'agora_channel_name', 'agora_token', 'agora_app_id',
                'title', 'terms_conditions', 'information'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('live_video_items', function (Blueprint $table) {
            $table->id();
            foreach (['live_video_id', 'title_ar', 'status', 'user_id', 'seller_id', 'video', 'address',
                'type', 'image', 'category_id', 'information_ar', 'bidding', 'terms', 'color_id',
                'terms_ar', 'quantity', 'piece_multiplier_number', 'health_certificate', 'title',
                'information', 'finished_price'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('video_views', function (Blueprint $table) {
            $table->id();
            $table->integer('video_id');
        });
        require_once database_path('migrations/2026_06_18_130000_create_live_video_item_pieces_table.php');
        (new \CreateLiveVideoItemPiecesTable())->up();
        DB::table('user_subscriptions')->insert(['user_id' => 1, 'status' => 'approved',
            'expires_at' => now()->addMonth(), 'remaining_auctions' => 3]);
        $this->postJson('/api/live/add', $this->auction() + ['video_type' => 'photo'])
            ->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('live_videos', 1);
        $this->assertDatabaseHas('user_subscriptions', ['remaining_auctions' => 2]);
        $invalid = $this->auction();
        unset($invalid['commission_payer']);
        $this->postJson('/api/live/add', $invalid)->assertJsonPath('success', false);
        $this->assertDatabaseCount('live_videos', 1);
        $this->assertDatabaseHas('user_subscriptions', ['remaining_auctions' => 2]);
        $animal = ['lineage_title_ar' => ['Animal'], 'weight' => [12.75],
            'address' => ['Address'], 'information_ar' => ['Details']];
        $this->post('/api/live/items/add/1', $animal)->assertStatus(422);
        $this->assertDatabaseCount('live_video_items', 0);
        $this->assertDatabaseCount('live_video_item_pieces', 0);
        $this->post('/api/live/items/add/1', $animal + ['image' => [[UploadedFile::fake()->image('ok.jpg')]]])
            ->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('live_video_items', 1);
        $this->assertDatabaseHas('live_video_item_pieces', ['weight' => 12.75]);
        $savedImages = DB::table('live_video_items')->value('image');
        $paths = json_decode($savedImages, true);
        try {
            $this->assertFileExists(storage_path('app/public/'.$paths[0]));
            $this->postJson('/api/live/items/update/1', ['information_ar' => 'Updated'])
                ->assertOk()->assertJsonPath('success', true);
            $this->assertSame($savedImages, DB::table('live_video_items')->value('image'));
            $filesBefore = glob(storage_path('app/public/image_video_item/*'));
            $this->post('/api/live/items/update/1', ['image' => [UploadedFile::fake()->create('bad.pdf', 10)]])
                ->assertStatus(422);
            $this->assertSame($savedImages, DB::table('live_video_items')->value('image'));
            $this->postJson('/api/live/items/pieces/add/1', ['weight' => 0])->assertStatus(422);
            $this->assertDatabaseCount('live_video_item_pieces', 1);
            $this->postJson('/api/live/items/pieces/add/1', ['weight' => 20.5])
                ->assertOk()->assertJsonPath('success', true);
            $this->assertDatabaseCount('live_video_item_pieces', 2);
            $this->post('/api/live/items/add/1', ['lineage_title_ar' => ['One', 'Two'], 'weight' => [10, 0],
                'image' => [[UploadedFile::fake()->image('one.jpg')], [UploadedFile::fake()->image('two.jpg')]]])
                ->assertStatus(422);
            $this->assertDatabaseCount('live_video_items', 1);
            $this->assertDatabaseCount('live_video_item_pieces', 2);
            $this->assertSame($filesBefore, glob(storage_path('app/public/image_video_item/*')));
        } finally {
            foreach ($paths as $path) {
                @unlink(storage_path('app/public/'.$path));
            }
        }
    }

    public function test_other_account_types_keep_optional_commission_fields(): void
    {
        auth('api')->user()->setRelation('admin', new Admin(['partner' => 'partner']));
        $data = $this->auction();
        unset($data['commission_amount'], $data['commission_payer']);
        $this->postJson('/validation/auction', $data)->assertOk()->assertJsonPath('success', true);
        auth('api')->user()->setRelation('admin', null);
        $this->postJson('/validation/auction', $data)->assertOk()->assertJsonPath('success', true);
    }

    public function test_arabic_validation_messages(): void
    {
        app()->setLocale('ar');
        $this->postJson('/validation/head', ['weight' => 0])->assertStatus(422)
            ->assertJsonPath('message', trans('validation.head_weight_positive'));
        $this->post('/validation/animal', ['lineage_title_ar' => ['Animal'], 'weight' => [10]])
            ->assertStatus(422)->assertJsonPath('message', trans('validation.animal_images_required'));
    }
}

