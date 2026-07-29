<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Regression guard: the admin name in the header dropdown had a hardcoded
 * Bootstrap "text-white" class left over from the old dark-header design.
 * Once the header background became light (theme.css), that made the name
 * render white-on-white — invisible except for its hover-state background.
 * The header already has ".header .nav-link { color: var(--md-text) !important; }"
 * in theme.css; removing the inline override lets that color apply correctly.
 */
class HeaderNameColorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_header_admin_name_span_has_no_hardcoded_white_text_class()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString($admin->name, $html);
        $this->assertStringNotContainsString('d-lg-inline text-white fw-semibold', $html);
    }
}
