<?php

namespace Tests\Feature\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LiveVideoSchemaDriftTest extends TestCase
{
    private $previousConnection;

    protected function setUp(): void
    {
        parent::setUp();
        $database = env('SCHEMA_TEST_DATABASE');
        if (! is_string($database) || ! preg_match('/^mazadat_schema_test_[a-z0-9_]+$/', $database)) {
            $this->markTestSkipped('Set SCHEMA_TEST_DATABASE to a dedicated mazadat_schema_test_* database.');
        }
        $this->previousConnection = DB::getDefaultConnection();
        $connection = config('database.connections.mysql');
        $connection['database'] = $database;
        $connection['url'] = null;
        config(['database.connections.schema_verification' => $connection]);
        DB::setDefaultConnection('schema_verification');
        Schema::dropIfExists('live_videos');
        Schema::create('live_videos', function (Blueprint $table) {
            $table->id();
            $table->string('sentinel')->nullable()->unique();
            $table->foreignId('parent_id')->nullable()->constrained('live_videos');
        });
    }

    protected function tearDown(): void
    {
        if ($this->previousConnection !== null) {
            Schema::dropIfExists('live_videos');
            DB::setDefaultConnection($this->previousConnection);
            DB::purge('schema_verification');
        }
        parent::tearDown();
    }

    /** @dataProvider existingColumns */
    public function test_additive_migration_preserves_schema_data_and_rollback(bool $titleExists, bool $cityExists): void
    {
        Schema::table('live_videos', function (Blueprint $table) use ($titleExists, $cityExists) {
            if ($titleExists) {
                $table->string('title', 300)->nullable();
            }
            if ($cityExists) {
                $table->integer('city_id')->nullable()->index();
            }
        });
        $row = ['sentinel' => 'preserved'];
        if ($titleExists) {
            $row['title'] = str_repeat('a', 300);
        }
        if ($cityExists) {
            $row['city_id'] = -7;
        }
        DB::table('live_videos')->insert($row);
        $migration = require database_path('migrations/2026_10_22_000000_add_missing_columns_to_live_videos_table.php');
        $migration->up();
        $migration->up();
        $columns = collect(DB::select('SHOW COLUMNS FROM live_videos'))->keyBy('Field');
        $this->assertSame('varchar(300)', $columns['title']->Type);
        $this->assertSame('YES', $columns['title']->Null);
        $this->assertMatchesRegularExpression('/^int(?:\(11\))?$/', $columns['city_id']->Type);
        $this->assertSame('YES', $columns['city_id']->Null);
        $this->assertNull($columns['title']->Default);
        $this->assertNull($columns['city_id']->Default);
        $indexes = collect(DB::select('SHOW INDEX FROM live_videos'))->pluck('Key_name')->unique()->all();
        $this->assertContains('live_videos_sentinel_unique', $indexes);
        if ($cityExists) {
            $this->assertContains('live_videos_city_id_index', $indexes);
        }
        $migration->down();
        $migration->up();
        $this->assertEquals($row, (array) DB::table('live_videos')->select(array_keys($row))->first());
        $this->assertTrue(Schema::hasColumns('live_videos', ['title', 'city_id']));
        $definition = (array) DB::selectOne('SHOW CREATE TABLE live_videos');
        $this->assertStringContainsString('CONSTRAINT `live_videos_parent_id_foreign`', $definition['Create Table']);
    }

    public function test_existing_column_definitions_are_not_rewritten(): void
    {
        Schema::table('live_videos', function (Blueprint $table) {
            $table->string('title', 255)->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
        });
        $before = DB::select('SHOW COLUMNS FROM live_videos');
        $migration = require database_path('migrations/2026_10_22_000000_add_missing_columns_to_live_videos_table.php');
        $migration->up();
        $migration->down();
        $this->assertEquals($before, DB::select('SHOW COLUMNS FROM live_videos'));
    }

    public static function existingColumns(): array
    {
        return [[false, false], [true, true], [true, false], [false, true]];
    }

    /** @dataProvider tokenLayouts */
    public function test_streaming_rollback_handles_current_legacy_and_mixed_layouts(bool $current, bool $legacy): void
    {
        require_once database_path('migrations/New folder/2025_10_16_000001_add_streaming_fields_to_live_videos_table.php');
        $migration = new \AddStreamingFieldsToLiveVideosTable();
        if ($current) {
            $migration->up();
        } else {
            Schema::table('live_videos', function (Blueprint $table) {
                $table->string('agora_channel_name')->nullable()->unique();
                $table->string('agora_app_id')->nullable();
            });
        }
        if ($legacy) {
            Schema::table('live_videos', function (Blueprint $table) {
                $table->text('agora_token_publisher')->nullable();
                $table->text('agora_token_subscriber')->nullable();
            });
        }
        $row = ['sentinel' => 'kept'];
        if ($legacy) {
            $row += ['agora_token_publisher' => 'publisher', 'agora_token_subscriber' => 'subscriber'];
        }
        DB::table('live_videos')->insert($row);
        $migration->down();
        $migration->down();
        foreach (['agora_channel_name', 'agora_token', 'agora_app_id'] as $column) {
            $this->assertFalse(Schema::hasColumn('live_videos', $column));
        }
        $this->assertEquals($row, (array) DB::table('live_videos')->select(array_keys($row))->first());
        $migration->up();
        $this->assertTrue(Schema::hasColumn('live_videos', 'agora_token'));
    }

    public static function tokenLayouts(): array
    {
        return [[true, false], [false, true], [true, true]];
    }
}
