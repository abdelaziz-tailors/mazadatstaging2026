<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\AuctionController;
use App\Models\Admin;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Per explicit request, the "Add Product" (+) icon that used to live inside
 * the Auctions table's actions column now has its own dedicated "القطع"
 * column, inserted right before the actions column so actions stays the
 * last column in the table. The auction show page also gained a real piece
 * count row (LiveVideo::video_items()->count() — the same query already
 * used for the list page's "products_count" column).
 */
class AuctionsPiecesColumnTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(array $permissions = []): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    private function createAuctionOwner(): User
    {
        return User::create([
            'name' => 'Organizer',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
        ]);
    }

    private function rowFor(LiveVideo $liveVideo): array
    {
        $request = Request::create('/admin/auctions/getData', 'POST', ['draw' => 1, 'start' => 0, 'length' => 50]);
        app()->instance('request', $request);

        $response = (new AuctionController())->get_data($request);
        $rows = collect(json_decode($response->getContent(), true)['data']);

        return $rows->firstWhere('id', $liveVideo->id);
    }

    public function test_get_data_includes_a_pieces_action_column_with_the_add_product_icon()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $liveVideo = LiveVideo::create(['title' => 'Auction']);

        $row = $this->rowFor($liveVideo);

        $this->assertArrayHasKey('pieces_action', $row);
        $this->assertStringContainsString('fa-plus', $row['pieces_action']);
        $this->assertStringContainsString(route('admin.products.create', $liveVideo->id), $row['pieces_action']);
    }

    public function test_pieces_column_is_rendered_before_the_actions_column_in_the_index_page()
    {
        Auth::guard('admin')->setUser($this->createAdmin(['view videos']));
        view()->share('errors', new ViewErrorBag());

        $html = (new AuctionController())->index(new Request())->render();

        $piecesHeaderPos = strpos($html, "data: 'pieces_action'");
        $actionHeaderPos = strpos($html, "data: 'action'");

        $this->assertNotFalse($piecesHeaderPos);
        $this->assertNotFalse($actionHeaderPos);
        $this->assertTrue($piecesHeaderPos < $actionHeaderPos, 'pieces_action column must come before the action column');
    }

    public function test_auction_show_page_displays_the_real_piece_count()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $liveVideo = LiveVideo::create(['title' => 'Auction', 'user_id' => $this->createAuctionOwner()->id]);
        LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'title' => 'Piece 1']);
        LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'title' => 'Piece 2']);

        $html = (new AuctionController())->show($liveVideo->id)->render();

        $this->assertEquals(2, $liveVideo->video_items()->count());
        $this->assertStringContainsString((string) 2, $html);
    }

    public function test_auction_show_page_displays_zero_pieces_when_none_exist()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $liveVideo = LiveVideo::create(['title' => 'Empty Auction', 'user_id' => $this->createAuctionOwner()->id]);

        $html = (new AuctionController())->show($liveVideo->id)->render();

        $this->assertEquals(0, $liveVideo->video_items()->count());
        $this->assertStringContainsString('>0<', $html);
    }
}
