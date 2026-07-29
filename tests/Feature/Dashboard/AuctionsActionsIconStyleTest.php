<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\AuctionController;
use App\Models\Admin;
use App\Models\LiveVideo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * The Auctions table's row actions were restyled to round icon buttons
 * (view + edit), scoped to this table only — the shared "videos.actions"
 * partial (used by the separate /admin/videos table) keeps its original
 * text-button style untouched.
 *
 * The kebab dropdown (originally holding "Add Product" + "view Product") was
 * removed per explicit request: "view Product" was redundant with the
 * standalone view icon. "Add Product" was a standalone icon in this same
 * column for a while, then moved out into its own dedicated "القطع" table
 * column per a later explicit request (see AuctionController::get_data()'s
 * "pieces_action" column and AuctionsPiecesColumnTest) — this actions
 * column now only holds view + edit.
 */
class AuctionsActionsIconStyleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_actions_partial_renders_icon_buttons_not_the_old_text_buttons()
    {
        Auth::guard('admin')->setUser(Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]));
        view()->share('errors', new ViewErrorBag());

        $liveVideo = LiveVideo::create(['title' => 'Auction']);

        $html = view('dashboard.pages.auctions.actions', ['item' => $liveVideo])->render();

        $this->assertStringContainsString('md-icon-btn', $html);
        $this->assertStringContainsString('fa-pen', $html);
        $this->assertStringContainsString('fa-eye', $html);
        // "Add Product" (fa-plus) moved out into its own "القطع" column.
        $this->assertStringNotContainsString('fa-plus', $html);
        $this->assertStringNotContainsString('fa-ellipsis-vertical', $html);
        $this->assertStringNotContainsString('dropdown-menu', $html);
        // Font Awesome only — feathericon's CSS isn't linked in the dashboard
        // layout, so any leftover "fe fe-*" icon would silently render nothing.
        $this->assertStringNotContainsString('class="fe fe-', $html);
    }

    /**
     * Regression guard: the edit/view icons were originally given colored
     * variants (blue/green). Per explicit request, matching the design
     * reference, they're plain monochrome (white circle, dark icon) — no
     * "md-icon-btn-info"/"md-icon-btn-success" color modifiers.
     */
    public function test_edit_and_view_icons_are_plain_monochrome_not_colored()
    {
        Auth::guard('admin')->setUser(Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]));
        view()->share('errors', new ViewErrorBag());

        $liveVideo = LiveVideo::create(['title' => 'Auction']);

        $html = view('dashboard.pages.auctions.actions', ['item' => $liveVideo])->render();

        $this->assertStringNotContainsString('md-icon-btn-info', $html);
        $this->assertStringNotContainsString('md-icon-btn-success', $html);
        $this->assertStringNotContainsString('md-icon-btn-danger', $html);
    }

    public function test_actions_partial_still_links_to_the_same_underlying_routes()
    {
        Auth::guard('admin')->setUser(Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]));
        view()->share('errors', new ViewErrorBag());

        $liveVideo = LiveVideo::create(['title' => 'Auction']);

        $html = view('dashboard.pages.auctions.actions', ['item' => $liveVideo])->render();

        $this->assertStringContainsString(route('admin.videos.edit', $liveVideo->id), $html);
        $this->assertStringContainsString(route('admin.auctions.show', $liveVideo->id), $html);
        // "Add Product" moved out into its own "القطع" column — no longer
        // part of this actions partial.
        $this->assertStringNotContainsString(route('admin.products.create', $liveVideo->id), $html);
        // "view Product" (admin.products.index) was dropped — redundant with
        // the standalone view icon above, per explicit request.
        $this->assertStringNotContainsString(route('admin.products.index', $liveVideo->id), $html);
    }

    /**
     * The view icon must be the first DOM child (rightmost in RTL) with
     * edit second — matching the view→edit convention used on other tables
     * (partners, vendors, users).
     */
    public function test_view_icon_is_first_and_edit_is_second_in_dom_order()
    {
        Auth::guard('admin')->setUser(Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]));
        view()->share('errors', new ViewErrorBag());

        $liveVideo = LiveVideo::create(['title' => 'Auction']);

        $html = view('dashboard.pages.auctions.actions', ['item' => $liveVideo])->render();

        $viewPos = strpos($html, route('admin.auctions.show', $liveVideo->id));
        $editPos = strpos($html, route('admin.videos.edit', $liveVideo->id));

        $this->assertNotFalse($viewPos);
        $this->assertNotFalse($editPos);
        $this->assertTrue($viewPos < $editPos);
    }

    public function test_index_page_has_a_descriptive_search_placeholder()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view videos', 'guard_name' => 'admin']);
        $admin->givePermissionTo($permission);
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new AuctionController())->index(new Request())->render();

        $this->assertStringContainsString('searchPlaceholder', $html);
        $this->assertStringContainsString(TranslationHelper::translate('search_auctions_placeholder'), $html);
    }
}
