<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\AuctionController;
use App\Models\Admin;
use App\Models\LiveVideo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Hits the controller directly rather than the HTTP route — see the note in
 * OrderControllerStatsTest for why (a pre-existing dashboard-locale redirect
 * quirk unrelated to this feature).
 */
class AuctionControllerStatsTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdminWithViewVideosPermission(): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        $permission = Permission::firstOrCreate([
            'name' => 'view videos',
            'guard_name' => 'admin',
        ]);
        $admin->givePermissionTo($permission);

        return $admin;
    }

    /**
     * The stats are a global count over a real, shared testing database, so
     * assertions compare before/after deltas instead of exact totals.
     */
    public function test_index_computes_correct_auction_stats()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewVideosPermission());

        $baseline = LiveVideo::query()->selectRaw("
            count(*) as total,
            sum(status = 'start') as in_progress,
            sum(status = 'end') as archived,
            sum(status is null or status not in ('start', 'end')) as upcoming
        ")->first();

        LiveVideo::create(['title' => 'Live now', 'status' => 'start']);
        LiveVideo::create(['title' => 'Finished', 'status' => 'end']);
        LiveVideo::create(['title' => 'Finished 2', 'status' => 'end']);
        LiveVideo::create(['title' => 'Not started yet', 'status' => null]);

        $view = (new AuctionController())->index(request());
        $stats = $view->getData()['stats'];

        $this->assertEquals((int) $baseline->total + 4, $stats['total']);
        $this->assertEquals((int) $baseline->in_progress + 1, $stats['in_progress']);
        $this->assertEquals((int) $baseline->archived + 2, $stats['archived']);
        $this->assertEquals((int) $baseline->upcoming + 1, $stats['upcoming']);
    }

    public function test_index_renders_the_stat_brief_with_the_total_auctions_label()
    {
        Auth::guard('admin')->setUser($this->createAdminWithViewVideosPermission());
        view()->share('errors', new ViewErrorBag());

        $view = (new AuctionController())->index(request());

        $this->assertArrayHasKey('stats', $view->getData());
        $this->assertStringContainsString(TranslationHelper::translate('total_auctions'), $view->render());
    }

    public function test_index_aborts_when_admin_lacks_view_videos_permission()
    {
        $admin = Admin::create([
            'name' => 'No Permission Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
        Auth::guard('admin')->setUser($admin);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new AuctionController())->index(request());
    }
}
