<?php

namespace Tests\Feature\Api;

use App\Http\Requests\api\Video\AddVideoRequest;
use App\Http\Requests\api\Video\UpdateVideoRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
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
    public function test_subscriber_can_add_own_animal_without_seller_id(): void
    {
        $auction = \App\Models\LiveVideo::create(['user_id' => 1, 'type' => 'live']);
        Route::post('/validation/own-animal/{id}', function (StoreItemRequest $request) {
            return response()->json(['seller_id' => $request->input('seller_id.0')]);
        });

        foreach ([[], ['seller_id' => null], ['seller_id' => []], ['seller_id' => [null]], ['seller_id' => ['']]] as $sellerInput) {
            $this->post('/validation/own-animal/'.$auction->id, $sellerInput + [
                'lineage_title_ar' => ['Subscriber animal'],
                'image' => [[UploadedFile::fake()->image('animal.jpg')]],
            ])->assertOk()->assertJsonPath('seller_id', null);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-10-20 12:30:30', 'Asia/Kuwait'));
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20 12:30:30', 'Asia/Kuwait'));
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'app.timezone' => 'Asia/Kuwait', 'app.debug' => false]);
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
        Schema::create('live_videos', function (Blueprint $table) {
            $table->id();
            foreach (['user_id', 'admin_id', 'partner_id', 'title_ar', 'start_price', 'date_start_at',
                'date_end_at', 'time_start_at', 'time_end_at', 'status', 'image', 'information_ar',
                'terms_conditions_ar', 'city_id', 'type', 'partners_type', 'commission_amount',
                'commission_payer', 'service_fee', 'agora_channel_name', 'agora_token', 'agora_app_id',
                'title', 'terms_conditions', 'information', 'schedule_start_utc', 'schedule_end_utc', 'schedule_timezone'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('live_video_items', function (Blueprint $table) {
            $table->id();
            foreach (['live_video_id', 'title_ar', 'status', 'user_id', 'seller_id', 'video', 'address',
                'type', 'image', 'category_id', 'information_ar', 'bidding', 'terms', 'color_id',
                'terms_ar', 'quantity', 'piece_multiplier_number', 'health_certificate', 'title',
                'information', 'finished_price', 'user_finished_id', 'transferred_from_item_id'] as $field) { $table->string($field)->nullable(); }
            $table->timestamps();
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

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_combined_schedule_validation_and_no_writes(): void
    {
        foreach ([
            ['date_start_at' => '2026-10-20', 'time_start_at' => '12:29'],
            ['date_start_at' => '2026-10-19'],
            ['date_end_at' => '2026-10-31'],
            ['date_end_at' => '2026-11-01', 'time_end_at' => '09:59'],
            ['date_end_at' => '2026-11-01', 'time_end_at' => '10:00'],
            ['date_start_at' => null], ['time_start_at' => null],
            ['date_start_at' => '2026-02-30'], ['time_start_at' => '25:00'],
            ['date_end_at' => []], ['time_end_at' => 'not a time'],
        ] as $invalid) {
            $this->postJson('/validation/auction', array_replace($this->auction(), $invalid))
                ->assertOk()->assertJsonPath('success', false)->assertJsonStructure(['errors']);
        }
        foreach (['date_start_at', 'time_start_at', 'date_end_at', 'time_end_at'] as $field) {
            $data = $this->auction();
            unset($data[$field]);
            $this->postJson('/validation/auction', $data)->assertJsonPath('success', false)
                ->assertJsonStructure(['errors' => [$field]]);
        }
        $this->assertDatabaseCount('validation_writes', 0);
        $this->postJson('/validation/auction', array_replace($this->auction(), [
            'date_start_at' => '2026-10-20', 'time_start_at' => '12:31',
        ]))->assertJsonPath('success', true);
        $this->assertDatabaseCount('validation_writes', 1);

        // 21:30 UTC is 00:30 the following day in the established auction timezone.
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20 21:30:00', 'UTC'));
        foreach (['en', 'ar'] as $locale) {
            app()->setLocale($locale);
            $this->postJson('/validation/auction', array_replace($this->auction(), [
                'date_start_at' => '2026-10-21', 'time_start_at' => '00:29',
            ]))->assertJsonPath('errors.time_start_at.0', trans('validation.auction_start_future'));
        }
        $this->postJson('/validation/auction', array_replace($this->auction(), [
            'date_start_at' => '2026-10-21', 'time_start_at' => '23:59',
            'date_end_at' => '2026-10-22', 'time_end_at' => '00:01',
        ]))->assertJsonPath('success', true);
    }

    public function test_partial_scheduling_updates_use_stored_values(): void
    {
        DB::table('live_videos')->insert(['id' => 1, 'user_id' => 1, 'status' => 'pending',
            'date_start_at' => '2026-11-01', 'date_end_at' => '2026-11-01',
            'time_start_at' => '10:00:00', 'time_end_at' => '11:00:00']);
        Route::post('/validation/auction-update/{id}', function (UpdateVideoRequest $request) {
            DB::table('validation_writes')->insert(['kind' => 'update']);
            return ['success' => true];
        });
        $this->postJson('/validation/auction-update/1', ['time_end_at' => '09:59'])
            ->assertJsonPath('errors.time_end_at.0', trans('validation.auction_end_after_start'));
        $this->postJson('/validation/auction-update/1', ['date_start_at' => '2026-10-19'])
            ->assertJsonPath('success', false);
        $this->postJson('/validation/auction-update/1', ['time_start_at' => null])
            ->assertJsonStructure(['errors' => ['time_start_at']]);
        $this->postJson('/api/live/update/1', ['time_end_at' => '09:59'])
            ->assertJsonPath('success', false)->assertJsonStructure(['errors' => ['time_end_at']]);
        $this->assertDatabaseHas('live_videos', ['time_end_at' => '11:00:00']);
        $this->assertDatabaseCount('validation_writes', 0);
        $this->postJson('/validation/auction-update/1', ['time_end_at' => '12:00'])->assertJsonPath('success', true);
        $this->postJson('/validation/auction-update/1', ['title_ar' => 'Updated'])->assertJsonPath('success', true);
        DB::table('live_videos')->where('id', 1)->update(['status' => 'live']);
        $this->postJson('/api/live/update/1', ['time_end_at' => '12:00'])->assertJsonPath('success', false);
        $this->assertDatabaseHas('live_videos', ['time_end_at' => '11:00:00']);
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
        $data = ['lineage_title_ar' => ['Animal'], 'gender' => ['male']];
        foreach ([[], ['image' => []], ['image' => 'bad'], ['image' => [[]]],
            ['image' => [[UploadedFile::fake()->create('bad.pdf', 10, 'application/pdf')]]],
            ['image' => [[UploadedFile::fake()->image('big.jpg')->size(20001)]]],
            ['image' => [[new UploadedFile('', 'failed.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true)]]]] as $invalid) {
            $this->post('/api/live/items/add/1', array_merge($data, $invalid))
                ->assertStatus(422)->assertJsonPath('success', false);
        }
        $this->post('/validation/animal', ['lineage_title_ar' => ['One', 'Two'], 'gender' => ['male', 'female'],
            'image' => [[UploadedFile::fake()->image('one.jpg')]]])->assertStatus(422);
        $this->assertDatabaseCount('validation_writes', 0);
        $this->post('/validation/animal', $data + ['image' => [[UploadedFile::fake()->image('ok.jpg')]]])
            ->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('validation_writes', 1);
    }

    public function test_all_head_creation_shapes_reject_invalid_genders(): void
    {
        foreach ([0, -1, []] as $gender) {
            $this->postJson('/api/live/items/pieces/add/1', ['gender' => $gender])->assertStatus(422);
            $this->postJson('/validation/head', ['gender' => $gender])->assertStatus(422);
            $this->post('/validation/animal', ['lineage_title_ar' => ['Animal'], 'gender' => [$gender],
                'image' => [[UploadedFile::fake()->image('ok.jpg')]]])->assertStatus(422);
            $this->post('/validation/animal', ['lineage_title_ar' => ['Animal'], 'quantity' => [2],
                'pieces' => [[['gender' => 'male'], ['gender' => $gender]]],
                'image' => [[UploadedFile::fake()->image('ok.jpg')]]])->assertStatus(422);
            $this->postJson('/validation/head-update', ['gender' => $gender])->assertStatus(422);
            $this->postJson('/validation/animal-update', ['pieces' => [['gender' => $gender]]])->assertStatus(422);
        }
        $this->assertDatabaseCount('validation_writes', 0);
        $this->postJson('/validation/head', ['gender' => 'male'])->assertOk()->assertJsonPath('success', true);
        $this->post('/validation/animal', ['lineage_title_ar' => ['Animal'], 'quantity' => [2],
            'pieces' => [[['gender' => 'male'], ['gender' => 'male']]],
            'image' => [[UploadedFile::fake()->image('ok.png')]]])->assertOk()->assertJsonPath('success', true);
        $this->postJson('/validation/animal-update', ['information' => 'Updated'])->assertOk();
        $this->postJson('/validation/head-update', ['identifier' => 'Updated'])->assertOk();
        $this->assertDatabaseCount('validation_writes', 2);
    }

    public function test_gender_is_optional_for_single_and_nested_heads(): void
    {
        $data = ['lineage_title_ar' => ['Animal'], 'quantity' => [2], 'gender' => [null],
            'pieces' => [[['gender' => 'male'], ['age' => '2', 'gender' => 'male']]]];
        $this->post('/validation/animal', $data + ['image' => [[UploadedFile::fake()->image('ok.jpg')]]])
            ->assertOk()->assertJsonPath('success', true);
        unset($data['pieces'][0][1]['gender']);
        $this->post('/validation/animal', $data + ['image' => [[UploadedFile::fake()->image('ok.jpg')]]])
            ->assertOk()->assertJsonPath('success', true);
        $this->postJson('/validation/animal-update', ['age' => 2])->assertOk();
        foreach (['', null] as $gender) {
            $this->postJson('/validation/head', ['gender' => $gender])->assertOk()->assertJsonPath('success', true);
            $this->postJson('/validation/head-update', ['gender' => $gender])->assertOk();
            $this->postJson('/validation/animal-update', ['gender' => $gender])->assertOk();
        }
        $this->postJson('/validation/head', [])->assertOk()->assertJsonPath('success', true);
        $this->post('/validation/animal', ['lineage_title_ar' => ['Animal'],
            'image' => [[UploadedFile::fake()->image('ok.jpg')]]])->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('validation_writes', 6);
    }

    public function test_arabic_validation_messages_are_readable(): void
    {
        app()->setLocale('ar');
        $this->postJson('/validation/head', ['gender' => 123])->assertStatus(422)
            ->assertJsonPath('errors.gender.0', 'يجب أن يكون جنس الرأس نصًا.');
        $this->post('/validation/animal', ['lineage_title_ar' => ['Animal']])->assertStatus(422)
            ->assertJsonPath('message', 'يرجى رفع صورة واحدة على الأقل لكل حيوان.');
        $data = $this->auction();
        unset($data['commission_amount']);
        $this->postJson('/validation/auction', $data)
            ->assertJsonPath('errors.commission_amount.0', 'نسبة العمولة مطلوبة.');
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
            $table->timestamp('replaced_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('video_views', function (Blueprint $table) {
            $table->id();
            $table->integer('video_id');
        });
        foreach (['video_favorites' => 'video_id', 'live_video_item_favorites' => 'live_video_item_id'] as $name => $foreign) {
            Schema::create($name, function (Blueprint $table) use ($foreign) {
                $table->id(); $table->unsignedBigInteger($foreign); $table->unsignedBigInteger('user_id');
                $table->softDeletes();
            });
        }
        Schema::create('follow_users', function (Blueprint $table) {
            $table->id(); $table->integer('user_id'); $table->integer('follow_id'); $table->softDeletes();
        });
        Schema::create('friends', function (Blueprint $table) {
            $table->id(); $table->integer('user_id'); $table->integer('friend_id'); $table->string('type')->nullable(); $table->softDeletes();
        });
        require_once database_path('migrations/2026_06_18_130000_create_live_video_item_pieces_table.php');
        (new \CreateLiveVideoItemPiecesTable())->up();
        (require database_path('migrations/2026_11_04_000000_add_gender_to_live_video_item_pieces.php'))->up();
        DB::table('users')->insert(['id' => 1]);
        DB::table('user_subscriptions')->insert(['user_id' => 1, 'status' => 'approved',
            'expires_at' => now()->addMonth(), 'remaining_auctions' => 3]);
        $this->postJson('/api/live/add', array_replace($this->auction(), ['date_start_at' => '2026-10-19']))
            ->assertJsonPath('success', false)->assertJsonStructure(['errors' => ['time_start_at']]);
        $this->assertDatabaseCount('live_videos', 0);
        $this->assertDatabaseHas('user_subscriptions', ['remaining_auctions' => 3]);
        $this->postJson('/api/live/add', $this->auction() + ['video_type' => 'photo'])
            ->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('live_videos', 1);
        $this->assertDatabaseHas('user_subscriptions', ['remaining_auctions' => 2]);
        $invalid = $this->auction();
        unset($invalid['commission_payer']);
        $this->postJson('/api/live/add', $invalid)->assertJsonPath('success', false);
        $this->assertDatabaseCount('live_videos', 1);
        $this->assertDatabaseHas('user_subscriptions', ['remaining_auctions' => 2]);
        $animal = ['lineage_title_ar' => ['Animal'], 'gender' => ['male'],
            'address' => ['Address'], 'information_ar' => ['Details']];
        $this->post('/api/live/items/add/1', $animal)->assertStatus(422);
        $this->assertDatabaseCount('live_video_items', 0);
        $this->assertDatabaseCount('live_video_item_pieces', 0);
        $this->post('/api/live/items/add/1', $animal + ['image' => [[UploadedFile::fake()->image('ok.jpg')]]])
            ->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('live_video_items', 1);
        $this->assertDatabaseHas('live_video_item_pieces', ['gender' => 'male']);
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
            $this->postJson('/api/live/items/pieces/add/1', ['gender' => 123])->assertStatus(422);
            $this->assertDatabaseCount('live_video_item_pieces', 1);
            $this->postJson('/api/live/items/pieces/add/1', ['gender' => 'male'])
                ->assertOk()->assertJsonPath('success', true);
            $this->assertDatabaseCount('live_video_item_pieces', 2);
            $this->post('/api/live/items/add/1', ['lineage_title_ar' => ['One', 'Two'], 'gender' => ['male', 0],
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

    public function test_other_account_types_require_payer_but_keep_optional_commission_amount(): void
    {
        auth('api')->user()->setRelation('admin', new Admin(['partner' => 'partner']));
        $data = $this->auction();
        unset($data['commission_amount']);
        $this->postJson('/validation/auction', $data)->assertOk()->assertJsonPath('success', true);
        auth('api')->user()->setRelation('admin', null);
        $this->postJson('/validation/auction', $data)->assertOk()->assertJsonPath('success', true);
        unset($data['commission_payer']);
        $this->postJson('/validation/auction', $data)->assertOk()->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['commission_payer']]);
    }

    public function test_arabic_validation_messages(): void
    {
        app()->setLocale('ar');
        $this->postJson('/validation/head', ['gender' => 123])->assertStatus(422)
            ->assertJsonPath('message', trans('validation.head_gender_string'));
        $this->post('/validation/animal', ['lineage_title_ar' => ['Animal'], 'gender' => ['male']])
            ->assertStatus(422)->assertJsonPath('message', trans('validation.animal_images_required'));
    }
}

