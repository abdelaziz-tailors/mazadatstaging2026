<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\SearchController;
use App\Models\Admin;
use App\Models\LiveVideo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Admin header global search (/admin/search?q=...): auctions only, matched
 * by their EXACT number (LiveVideo.id) — not title, and not users/orders
 * (an earlier version searched all three; narrowed to auctions-by-number
 * per explicit follow-up request). The match must be exact, not a fuzzy
 * "contains" — an earlier version used LIKE "%q%", which meant searching
 * "1" also matched auctions #351, #341, #331 etc, not just #1.
 */
class SearchControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
    }

    public function test_finds_matching_auction_by_number()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $match = LiveVideo::create(['title' => 'Camel auction', 'title_ar' => 'مزاد الإبل النادر']);

        $view = (new SearchController())->index(new Request(['q' => (string) $match->id]));

        $this->assertTrue($view->getData()['auctions']->contains('id', $match->id));
    }

    /**
     * Regression guard: an earlier version matched with LIKE "%q%", so
     * searching "1" also returned auctions #351, #341, #331, etc — any id
     * containing that digit anywhere. An exact match can only ever return
     * the single row whose id equals the query, never several.
     */
    public function test_search_is_an_exact_id_match_not_a_fuzzy_contains_match()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $target = LiveVideo::create(['title' => 'Target']);
        LiveVideo::create(['title' => 'Decoy A']);
        LiveVideo::create(['title' => 'Decoy B']);

        $view = (new SearchController())->index(new Request(['q' => (string) $target->id]));
        $auctions = $view->getData()['auctions'];

        $this->assertCount(1, $auctions);
        $this->assertSame($target->id, $auctions->first()->id);
    }

    public function test_auction_title_text_does_not_match_anything()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        LiveVideo::create(['title' => 'Camel auction', 'title_ar' => 'مزاد الإبل النادر']);

        $view = (new SearchController())->index(new Request(['q' => 'الإبل']));

        $this->assertCount(0, $view->getData()['auctions']);
    }

    public function test_non_numeric_query_never_matches_any_auction()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $match = LiveVideo::create(['title' => 'Camel auction']);

        // Even a query that happens to be a substring of the real id, but
        // isn't itself purely numeric, must not match.
        $view = (new SearchController())->index(new Request(['q' => $match->id . 'x']));

        $this->assertCount(0, $view->getData()['auctions']);
    }

    public function test_empty_query_returns_no_results()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        LiveVideo::create(['title' => 'Something']);

        $view = (new SearchController())->index(new Request());

        $this->assertCount(0, $view->getData()['auctions']);
    }

    public function test_no_match_returns_an_empty_collection_not_an_error()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $view = (new SearchController())->index(new Request(['q' => '999999999']));

        $this->assertCount(0, $view->getData()['auctions']);
    }

    public function test_search_page_renders_without_errors()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $match = LiveVideo::create(['title' => 'Rendersafe', 'title_ar' => 'اختبار العرض']);

        $html = (new SearchController())->index(new Request(['q' => (string) $match->id]))->render();

        $this->assertStringContainsString('اختبار العرض', $html);
    }

    public function test_search_page_renders_a_no_results_message_when_nothing_matches()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new SearchController())->index(new Request(['q' => '999999999']))->render();

        $this->assertStringContainsString(TranslationHelper::translate('no_results_found'), $html);
    }

    public function test_search_scopes_auctions_to_the_partner_admins_own_auctions()
    {
        $partnerAdmin = Admin::create([
            'name' => 'Partner Admin',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
        ]);
        Auth::guard('admin')->setUser($partnerAdmin);
        view()->share('errors', new ViewErrorBag());

        $otherAdmin = $this->createSuperAdmin();
        $mine = LiveVideo::create(['title' => 'Mine', 'title_ar' => 'مزادي', 'admin_id' => $partnerAdmin->id]);
        $notMine = LiveVideo::create(['title' => 'Not mine', 'title_ar' => 'مش بتاعي', 'admin_id' => $otherAdmin->id]);

        $view = (new SearchController())->index(new Request(['q' => (string) $mine->id]));
        $ids = $view->getData()['auctions']->pluck('id');
        $this->assertTrue($ids->contains($mine->id));

        $otherView = (new SearchController())->index(new Request(['q' => (string) $notMine->id]));
        $this->assertFalse($otherView->getData()['auctions']->pluck('id')->contains($notMine->id));
    }
}
