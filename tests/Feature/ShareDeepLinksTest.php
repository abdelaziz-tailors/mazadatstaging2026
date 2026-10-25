<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShareDeepLinksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // A dedicated in-memory connection; never migrate or modify the configured database.
        config(['database.default' => 'share_test', 'database.connections.share_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ], 'deep_links.base_url' => 'https://share.example',
            'deep_links.legacy_android' => [], 'deep_links.legacy_ios' => [],
            'deep_links.android.apps' => [], 'deep_links.ios.apps' => [],
            'deep_links.ios_store_url' => 'https://store.example/ios',
            'deep_links.android_store_url' => 'https://store.example/android',
            'deep_links.auction_app_url' => null, 'deep_links.lot_app_url' => null]);
        Schema::create('live_videos', function (Blueprint $table) {
            $table->id();
            foreach (['title', 'title_ar', 'information', 'information_ar', 'image', 'status'] as $column) {
                $table->string($column)->nullable();
            }
        });
        Schema::create('live_video_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('live_video_id')->nullable();
            $table->unsignedBigInteger('transferred_from_item_id')->nullable();
            foreach (['title', 'title_ar', 'information', 'information_ar', 'image'] as $column) {
                $table->string($column)->nullable();
            }
        });
        Schema::create('users', function (Blueprint $table) {
            $table->softDeletes();
            $table->id();
            $table->string('name');
            $table->string('user_name')->nullable();
            $table->string('image')->nullable();
        });
        DB::table('live_videos')->insert(['id' => 1, 'title' => 'Auction <test>',
            'information' => '<b>Public description</b>', 'image' => '["auctions/cover.jpg"]', 'status' => 'start']);
        DB::table('live_video_items')->insert(['id' => 2, 'live_video_id' => 1,
            'title_ar' => 'Lot title', 'information_ar' => 'Lot description', 'image' => 'https://images.example/lot.jpg']);
    }

    public function test_auction_preview_is_public_escaped_and_has_store_fallbacks(): void
    {
        $this->get('/share/auction/1')->assertOk()
            ->assertSee('Auction &lt;test&gt;', false)
            ->assertSee('property="og:description" content="Public description"', false)
            ->assertSee('https://share.example/share/auction/1', false)
            ->assertSee('property="og:image"', false)
            ->assertSee('/auctions/cover.jpg', false)
            ->assertSee('https://store.example/ios', false)
            ->assertSee('https://store.example/android', false)
            ->assertDontSee('window.location', false);
    }

    public function test_lot_uses_exact_id_and_optional_confirmed_app_template(): void
    {
        config(['deep_links.lot_app_url' => 'testapp://auction/{auction_id}/lot/{id}']);
        $this->get('/share/lot/2')->assertOk()->assertSee('Lot title')
            ->assertSee('https://images.example/lot.jpg', false)
            ->assertSee('testapp://auction/1/lot/2', false);
    }

    public function test_ended_auction_and_lot_keep_their_landing_pages(): void
    {
        DB::table('live_videos')->where('id', 1)->update(['status' => 'end']);
        foreach (['auction/1', 'lot/2'] as $path) {
            $this->get('/share/' . $path)->assertOk()->assertSee('This auction has ended.');
        }
    }

    public function test_transferred_and_orphan_lots_are_not_public(): void
    {
        DB::table('live_video_items')->insert(['id' => 3, 'live_video_id' => 1, 'transferred_from_item_id' => 2]);
        $this->get('/share/lot/2')->assertNotFound();
        DB::table('live_videos')->delete();
        $this->get('/share/lot/3')->assertNotFound();
    }

    public function test_transferred_copy_opens_its_current_auction_and_exact_lot_id(): void
    {
        DB::table('live_videos')->insert(['id' => 4, 'title' => 'Destination auction', 'status' => 'start']);
        DB::table('live_video_items')->insert(['id' => 3, 'live_video_id' => 4,
            'transferred_from_item_id' => 2, 'title_ar' => 'Transferred copy']);
        config(['deep_links.lot_app_url' => 'testapp://auction/{auction_id}/lot/{id}']);

        $this->get('/share/lot/2')->assertNotFound();
        $this->get('/share/lot/3')->assertOk()
            ->assertSee('Transferred copy')
            ->assertSee('https://share.example/share/lot/3', false)
            ->assertSee('testapp://auction/4/lot/3', false)
            ->assertDontSee('testapp://auction/1/lot/2', false);
        $this->assertSame('/share/auction/4', route('shared.auction', ['id' => 4], false));
        $this->assertSame('/share/lot/3', route('shared.lot', ['id' => 3], false));
    }

    public function test_invalid_and_missing_ids_return_404(): void
    {
        foreach (['auction', 'lot'] as $type) {
            foreach (['nope', '-1', '0', '9999999999999999999999999', '99'] as $id) {
                $this->get('/share/' . $type . '/' . $id)->assertNotFound();
            }
        }
    }

    public function test_missing_optional_settings_and_images_render_cleanly(): void
    {
        config(['deep_links.ios_store_url' => null, 'deep_links.android_store_url' => null]);
        DB::table('live_videos')->where('id', 1)->update(['image' => null, 'status' => null]);
        $this->get('/share/auction/1')->assertOk()->assertDontSee('og:image', false)
            ->assertDontSee('Download for');
    }

    public function test_profile_route_is_preserved(): void
    {
        DB::table('users')->insert(['id' => 7, 'name' => 'Existing profile']);
        $this->get('/u/7')->assertOk()->assertSee('Existing profile');
        $this->assertSame('/u/7', route('shared.profile', ['id' => 7], false));
    }

    public function test_associations_use_configuration_on_all_existing_routes(): void
    {
        $paths = ['/u/*', '/share/auction/*', '/share/lot/*'];
        config(['deep_links.android.package_name' => 'example.test.app',
            'deep_links.android.sha256_cert_fingerprints' => [str_repeat('AB:', 31) . 'AB'],
            'deep_links.ios.team_id' => 'TESTTEAM', 'deep_links.ios.bundle_ids' => ['example.test.app'],
            'deep_links.applinks.paths' => $paths]);
        $this->get('/.well-known/assetlinks.json')->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('0.target.package_name', 'example.test.app')
            ->assertJsonPath('0.relation.0', 'delegate_permission/common.handle_all_urls');
        foreach (['/apple-app-site-association', '/.well-known/apple-app-site-association'] as $url) {
            $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/json')
                ->assertJsonPath('applinks.details.0.appID', 'TESTTEAM.example.test.app')
                ->assertJsonPath('applinks.details.0.paths', $paths);
        }
    }

    public function test_legacy_associations_are_preserved_and_no_public_files_shadow_routes(): void
    {
        $android = json_decode(file_get_contents(config_path('deep_link_associations/assetlinks.json')), true);
        $ios = json_decode(file_get_contents(config_path('deep_link_associations/apple-app-site-association.json')), true);
        config(['deep_links.legacy_android' => $android, 'deep_links.legacy_ios' => $ios['applinks']['details'],
            'deep_links.android.package_name' => null, 'deep_links.android.sha256_cert_fingerprints' => [],
            'deep_links.ios.team_id' => 'TESTTEAM', 'deep_links.ios.bundle_ids' => ['example.share.app'],
            'deep_links.applinks.paths' => ['/u/*']]);
        $this->assertFileDoesNotExist(public_path('.well-known/assetlinks.json'));
        $this->assertFileDoesNotExist(public_path('.well-known/apple-app-site-association'));
        $this->assertFileDoesNotExist(public_path('apple-app-site-association'));
        $response = $this->get('/.well-known/assetlinks.json')->assertOk()
            ->assertHeader('Content-Type', 'application/json')->assertExactJson($android);
        $this->assertFalse($response->headers->has('Location'));
        $this->assertCount(3, $response->json('0.target.sha256_cert_fingerprints'));
        foreach ($response->json('0.target.sha256_cert_fingerprints') as $fingerprint) {
            $this->assertMatchesRegularExpression('/^(?:[A-F0-9]{2}:){31}[A-F0-9]{2}$/D', $fingerprint);
        }
        $root = $this->get('/apple-app-site-association')->assertOk()->assertHeader('Content-Type', 'application/json');
        $wellKnown = $this->get('/.well-known/apple-app-site-association')->assertOk()
            ->assertHeader('Content-Type', 'application/json');
        $this->assertSame($root->json(), $wellKnown->json());
        $this->assertFalse($root->headers->has('Location'));
        $this->assertFalse($wellKnown->headers->has('Location'));
        $this->assertSame($ios['applinks']['details'][0], $root->json('applinks.details.0'));
        $root->assertJsonPath('applinks.details.1.appID', 'TESTTEAM.example.share.app')
            ->assertJsonPath('applinks.details.1.paths', ['/u/*', '/share/auction/*', '/share/lot/*']);
    }

    public function test_multiple_apps_merge_without_losing_certificates_or_paths(): void
    {
        $fingerprint = str_repeat('AB:', 31) . 'AB';
        $other = str_repeat('CD:', 31) . 'CD';
        config(['deep_links.legacy_android' => [[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => ['namespace' => 'android_app', 'package_name' => 'example.legacy',
                'sha256_cert_fingerprints' => [$fingerprint]],
        ]], 'deep_links.android.package_name' => 'example.legacy',
            'deep_links.android.sha256_cert_fingerprints' => [$other, $fingerprint, 'invalid'],
            'deep_links.android.apps' => [['package_name' => 'example.share', 'sha256_cert_fingerprints' => [$other]]],
            'deep_links.legacy_ios' => [['appID' => 'TESTTEAM.example.legacy', 'paths' => ['/reels/*']]],
            'deep_links.ios.team_id' => 'TESTTEAM', 'deep_links.ios.bundle_ids' => ['example.legacy'],
            'deep_links.ios.apps' => [['appID' => 'OTHERTEAM.example.share', 'paths' => ['/share/lot/*']]],
            'deep_links.applinks.paths' => ['/u/*']]);
        $this->get('/.well-known/assetlinks.json')->assertOk()
            ->assertJsonCount(2)->assertJsonPath('0.target.sha256_cert_fingerprints', [$fingerprint, $other])
            ->assertJsonPath('1.target.package_name', 'example.share');
        $this->get('/.well-known/apple-app-site-association')->assertOk()
            ->assertJsonCount(2, 'applinks.details')
            ->assertJsonPath('applinks.details.0.paths', ['/reels/*', '/u/*', '/share/auction/*', '/share/lot/*'])
            ->assertJsonPath('applinks.details.1', ['appID' => 'OTHERTEAM.example.share', 'paths' => ['/share/lot/*']]);
    }

    public function test_verified_mazadat_identities_coexist_with_legacy_apps_without_cache(): void
    {
        $legacyAndroid = json_decode(file_get_contents(config_path('deep_link_associations/assetlinks.json')), true);
        $legacyIos = json_decode(file_get_contents(config_path('deep_link_associations/apple-app-site-association.json')), true);
        $fingerprint = '95:6F:EB:6B:22:37:B4:53:1A:94:9D:68:E3:BC:F5:F6:0C:DC:A6:38:84:B8:A4:5D:4B:F8:48:C8:68:E5:AC:1C';
        config(['deep_links.legacy_android' => $legacyAndroid,
            'deep_links.legacy_ios' => $legacyIos['applinks']['details'],
            'deep_links.android.package_name' => 'com.anaamworld.app',
            'deep_links.android.sha256_cert_fingerprints' => [$fingerprint],
            'deep_links.ios.team_id' => 'S2QF6L6CJZ',
            'deep_links.ios.bundle_ids' => ['com.anaamworldd.app'],
            'deep_links.applinks.paths' => ['/u/*']]);
        $android = $this->get('/.well-known/assetlinks.json')->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('X-LiteSpeed-Cache-Control', 'no-cache')
            ->assertJsonPath('0', $legacyAndroid[0])
            ->assertJsonPath('1.target.package_name', 'com.anaamworld.app')
            ->assertJsonPath('1.target.sha256_cert_fingerprints', [$fingerprint]);
        $this->assertStringContainsString('no-store', $android->headers->get('Cache-Control'));
        $this->assertFalse($android->headers->has('Location'));
        $responses = [];
        foreach (['/.well-known/apple-app-site-association', '/apple-app-site-association'] as $url) {
            $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/json')
                ->assertHeader('X-LiteSpeed-Cache-Control', 'no-cache')
                ->assertJsonPath('applinks.details.0', $legacyIos['applinks']['details'][0])
                ->assertJsonPath('applinks.details.1.appID', 'S2QF6L6CJZ.com.anaamworldd.app')
                ->assertJsonPath('applinks.details.1.paths', ['/u/*', '/share/auction/*', '/share/lot/*']);
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $this->assertFalse($response->headers->has('Location'));
            $responses[] = $response->getContent();
        }
        $this->assertSame($responses[0], $responses[1]);
    }

    public function test_missing_association_configuration_does_not_claim_an_app(): void
    {
        config(['deep_links.android.package_name' => null, 'deep_links.android.sha256_cert_fingerprints' => [],
            'deep_links.ios.team_id' => null, 'deep_links.ios.bundle_ids' => []]);
        $this->get('/.well-known/assetlinks.json')->assertOk()->assertExactJson([]);
        $this->get('/.well-known/apple-app-site-association')->assertOk()
            ->assertJsonPath('applinks.details', []);
    }
}
