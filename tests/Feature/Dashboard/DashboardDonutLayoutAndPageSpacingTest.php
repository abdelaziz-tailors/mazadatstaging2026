<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Admin;
use App\Models\LiveVideo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Per explicit follow-up requests:
 *  - Inside the "auctions by status" donut card, the ring now comes before
 *    the legend list in the DOM (in RTL, first DOM child renders rightmost,
 *    so the ring reads first/rightmost, legend second/to its left) — this
 *    is purely an internal reorder within that one card, unrelated to the
 *    card-to-card ordering already covered by DashboardHomeLayoutOrderTest.
 *  - The donut+legend row is horizontally centered in the card instead of
 *    stretching edge to edge (the legend list no longer force-grows via
 *    flex:1).
 *  - The dashboard's overall content area had no bottom padding at all
 *    (custom.css's ".page-wrapper > .content" pads top/sides only), so the
 *    last row sat flush against the browser edge — fixed globally.
 */
class DashboardDonutLayoutAndPageSpacingTest extends TestCase
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

    public function test_donut_ring_comes_before_the_legend_list_in_the_dom()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());
        LiveVideo::create(['title' => 'Auction', 'status' => 'start']);

        $html = (new DashboardController())->index()->render();

        $donutWrapPos = strpos($html, 'md-status-donut-wrap');
        $legendListPos = strpos($html, 'md-status-legend-list');

        $this->assertNotFalse($donutWrapPos);
        $this->assertNotFalse($legendListPos);
        $this->assertTrue($donutWrapPos < $legendListPos, 'the donut ring should come before the legend list in the DOM');
    }

    public function test_donut_row_is_centered_and_legend_no_longer_force_grows()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css);

        $rowPos = strpos($css, '.md-status-donut-row {');
        $this->assertNotFalse($rowPos);
        $rowBlock = substr($css, $rowPos, 150);
        $this->assertStringContainsString('justify-content: center;', $rowBlock);

        $legendPos = strpos($css, '.md-status-legend-list {');
        $this->assertNotFalse($legendPos);
        $legendBlock = substr($css, $legendPos, 200);
        $this->assertStringContainsString('flex: 0 1 auto;', $legendBlock);
        $this->assertStringNotContainsString('flex: 1;', $legendBlock);
    }

    public function test_content_wrapper_has_real_bottom_padding()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css);

        $pos = strpos($css, '.page-wrapper > .content {');
        $this->assertNotFalse($pos);
        $block = substr($css, $pos, 100);
        $this->assertStringContainsString('padding-bottom: 1.875rem;', $block);
    }

    /**
     * Per explicit follow-up: the donut+legend block sat flush at the top of
     * the card, leaving a large empty gap below it since the card matches
     * its taller line-chart siblings' height. Now vertically centered.
     */
    public function test_donut_card_body_centers_its_content_vertically()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());
        LiveVideo::create(['title' => 'Auction', 'status' => 'start']);

        $html = (new DashboardController())->index()->render();
        $this->assertStringContainsString('card-body md-donut-card-body', $html);

        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $pos = strpos($css, '.md-donut-card-body {');
        $this->assertNotFalse($pos);
        $block = substr($css, $pos, 150);
        $this->assertStringContainsString('align-items: center;', $block);
        $this->assertStringContainsString('justify-content: center;', $block);
    }

    /**
     * Per explicit follow-up ("فين صورة المزاد؟"): the auction image column
     * was already correct — it just showed the same generic person-shaped
     * placeholder as every other avatar in the dashboard when an auction has
     * no uploaded photo, which read as "no image column" rather than "empty
     * photo slot". Now uses a distinct picture-frame icon there instead.
     */
    public function test_latest_auctions_image_placeholder_uses_a_picture_icon_not_the_generic_user_icon()
    {
        Auth::guard('admin')->setUser($this->createSuperAdmin());
        view()->share('errors', new ViewErrorBag());
        LiveVideo::create(['title_ar' => 'مزاد بدون صورة', 'status' => 'start']);

        $html = (new DashboardController())->index()->render();

        $tablePos = strpos($html, TranslationHelper::translate('latest_auctions'));
        $this->assertNotFalse($tablePos);
        $tbodyPos = strpos($html, '<tbody>', $tablePos);
        $this->assertNotFalse($tbodyPos);
        $firstTdPos = strpos($html, '<td>', $tbodyPos);
        $firstTdEnd = strpos($html, '</td>', $firstTdPos);
        $firstTdContent = substr($html, $firstTdPos, $firstTdEnd - $firstTdPos);

        $this->assertStringContainsString('fa-solid fa-image', $firstTdContent);
        $this->assertStringNotContainsString('fa-solid fa-user', $firstTdContent);
    }
}
