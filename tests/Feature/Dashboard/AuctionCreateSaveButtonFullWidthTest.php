<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\VideoController;
use App\Models\Admin;
use App\Models\City;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Per explicit request: the auction "create" form's Save button
 * (/admin/videos/create) should span the full width of its container on
 * small screens instead of a narrow tap target — done via an opt-in
 * ".md-btn-full-sm" class + a max-width:575px media query in theme.css.
 */
class AuctionCreateSaveButtonFullWidthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_theme_css_defines_the_full_width_on_small_screens_rule()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');

        $pos = strpos($css, '.md-btn-full-sm');
        $this->assertNotFalse($pos, 'the .md-btn-full-sm rule should exist');

        $block = substr($css, max(0, $pos - 200), 400);
        $this->assertStringContainsString('@media (max-width: 575px)', $block);
        $this->assertStringContainsString('width: 100%;', $block);
    }

    public function test_auction_create_page_save_button_opts_into_the_full_width_class()
    {
        Auth::guard('admin')->setUser(Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]));
        view()->share('errors', new ViewErrorBag());
        City::create(['name' => json_encode(['ar' => 'جدة', 'en' => 'Jeddah']), 'is_active' => 1]);

        $html = (new VideoController())->create()->render();

        $this->assertMatchesRegularExpression(
            '/<button[^>]*name="action"[^>]*value="save"[^>]*class="btn btn-primary md-btn-full-sm"/',
            $html
        );
    }
}
