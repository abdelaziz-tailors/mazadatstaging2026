<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Regression guard: rtl.css was linked with no cache-busting query string,
 * unlike theme.css. A previous CSS fix to rtl.css (the mobile sidebar-open
 * rule) was correct and deployed, but browsers that had already cached the
 * old rtl.css under the same bare URL kept serving the stale copy — the fix
 * was invisible on any device that had visited the dashboard before the
 * deploy. Versioning the URL forces a re-fetch whenever the file changes.
 */
class RtlCssCacheBustingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_rtl_css_link_has_a_cache_busting_version_query_string()
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());
        app('laravellocalization')->setLocale('ar');

        $html = (new \App\Http\Controllers\Dashboard\DashboardController())->index()->render();

        $this->assertMatchesRegularExpression(
            '#dashboard/css/rtl\.css\?v=\d+#',
            $html,
            'rtl.css must be versioned so browsers re-fetch it after every deploy, not just theme.css'
        );
    }
}
