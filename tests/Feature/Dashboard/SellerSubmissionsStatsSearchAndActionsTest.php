<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\SellerSubmissionController;
use App\Models\Admin;
use App\Models\SellerSubmission;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * The seller-submissions list (/admin/seller-submissions) had no summary
 * "brief" stat cards (unlike Auctions/Orders/Users/Partners), its search box
 * used DataTables' raw untranslated English default ("Search:") with no
 * placeholder at all (no "language" block was configured), and its row
 * actions were a single generic dropdown instead of the round icon-button
 * component used elsewhere (view + "request edit" as standalone icons,
 * approve/reject kept in the kebab menu).
 */
class SellerSubmissionsStatsSearchAndActionsTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);
    }

    private function createSubmission(string $status = 'pending'): SellerSubmission
    {
        $partner = User::create([
            'name' => 'Partner',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
        ]);

        return SellerSubmission::create([
            'sheep_type' => 'Test sheep',
            'status' => $status,
            'partner_id' => $partner->id,
        ]);
    }

    public function test_index_computes_real_stats_from_the_database()
    {
        Auth::guard('admin')->setUser($this->createAdmin());

        $baseline = SellerSubmission::query()->selectRaw("
            count(*) as total,
            sum(status = 'approved') as approved,
            sum(status = 'rejected') as rejected,
            sum(status not in ('approved', 'rejected')) as under_review
        ")->first();

        $this->createSubmission('approved');
        $this->createSubmission('rejected');
        $this->createSubmission('rejected');
        $this->createSubmission('pending');
        $this->createSubmission('needs edit');

        $view = (new SellerSubmissionController())->index();
        $stats = $view->getData()['stats'];

        $this->assertEquals((int) $baseline->total + 5, $stats['total']);
        $this->assertEquals((int) $baseline->approved + 1, $stats['approved']);
        $this->assertEquals((int) $baseline->rejected + 2, $stats['rejected']);
        $this->assertEquals((int) $baseline->under_review + 2, $stats['under_review']);
    }

    public function test_index_page_renders_the_stat_cards()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new SellerSubmissionController())->index()->render();

        $this->assertStringContainsString(TranslationHelper::translate('rejected_submissions'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('approved_submissions'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('submissions_under_review'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('total_submissions'), $html);
    }

    public function test_search_box_has_an_arabic_label_and_a_descriptive_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new SellerSubmissionController())->index()->render();

        $this->assertStringContainsString('searchPlaceholder', $html);
        $this->assertStringContainsString(TranslationHelper::translate('search_seller_submissions_placeholder'), $html);
        // The old bug: no "language" block at all meant DataTables fell back
        // to its own hardcoded English default regardless of the site's
        // language — this asserts a real translated "search" value is wired
        // in instead of leaving that up to DataTables' own default.
        $this->assertStringContainsString('"search":', $html);
    }

    /**
     * Regression guard: this table had no custom "dom" layout at all (every
     * other DataTable in the dashboard has one), so it fell back to
     * DataTables' default two-column Bootstrap row — which visually centered
     * the search box instead of pinning it to the far edge like every other
     * table. Also carries the standard toolbar horizontal-padding fix.
     */
    public function test_index_page_has_the_standard_toolbar_dom_layout_and_padding()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new SellerSubmissionController())->index()->render();

        $this->assertStringContainsString(
            'd-flex flex-wrap justify-content-between align-items-center mb-3 px-2',
            $html
        );
        $this->assertStringContainsString('d-flex justify-content-between px-2', $html);
    }

    /**
     * Approve/Reject were pulled out of the kebab dropdown into standalone
     * icons alongside view/edit, per explicit request matching a design
     * reference — no dropdown at all anymore.
     */
    public function test_actions_partial_has_four_standalone_icons_view_edit_approve_reject()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $item = $this->createSubmission();

        $html = view('dashboard.pages.seller-submissions.actions', ['item' => $item])->render();

        $this->assertStringContainsString('md-icon-btn', $html);
        $this->assertStringContainsString(route('admin.seller-submissions.show', $item->id), $html);
        $this->assertStringContainsString('fa-eye', $html);
        $this->assertStringContainsString('fa-pen', $html);
        $this->assertStringContainsString('md-icon-btn-success', $html);
        $this->assertStringContainsString('fa-check', $html);
        $this->assertStringContainsString('md-icon-btn-danger', $html);
        $this->assertStringContainsString('fa-times', $html);
        $this->assertStringContainsString(route('admin.seller-submissions.approve', $item->id), $html);
        $this->assertStringNotContainsString('fa-ellipsis-vertical', $html);
        $this->assertStringNotContainsString('dropdown-menu', $html);
        $this->assertStringNotContainsString('btn-group', $html);
    }

    /**
     * A submission that's already approved must not show the Approve icon
     * again (matches the original @if($item->status !== 'approved') guard).
     */
    public function test_approve_icon_is_hidden_when_already_approved()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $item = $this->createSubmission('approved');

        $html = view('dashboard.pages.seller-submissions.actions', ['item' => $item])->render();

        $this->assertStringNotContainsString('md-icon-btn-success', $html);
        $this->assertStringNotContainsString('fa-check', $html);
        // Reject must still be there regardless of status.
        $this->assertStringContainsString('md-icon-btn-danger', $html);
    }
}
