<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\VideoController;
use App\Models\Admin;
use App\Models\City;
use App\Models\LiveVideo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Covers the redesigned "new/edit auction" forms on /admin/videos/create
 * and /admin/videos/{id}/edit — per explicit request, restyled visually to
 * match the vendor creation flow's page shell (centered card, page-title +
 * breadcrumb, page icon, hr divider), while keeping the auction form's own
 * much larger field set (multiple sections: details, schedule, banner,
 * fees, video type, partner) organized into a single wide card instead of
 * the old plain page-header block plus a second nested card. Only the
 * layout/markup changed — VideoController::create()/store()/edit()/
 * update() and every field the auction form collects are untouched.
 */
class VideoCreateFormDesignTest extends TestCase
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

    public function test_create_page_uses_the_vendor_create_page_shell()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new VideoController())->create()->render();

        $this->assertStringContainsString('md-page-icon', $html);
        $this->assertStringContainsString('page-title', $html);
        $this->assertStringContainsString('breadcrumb', $html);
        $this->assertStringContainsString('name="title_ar"', $html);
        $this->assertStringContainsString('name="date_start_at"', $html);
    }

    public function test_create_page_has_a_single_card_not_nested_cards()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new VideoController())->create()->render();

        $this->assertEquals(1, substr_count($html, 'class="card"'));
    }

    public function test_edit_page_uses_the_same_page_shell_and_prefills_data()
    {
        $admin = $this->createAdmin();
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $liveVideo = LiveVideo::create([
            'title' => 'Auction EN',
            'title_ar' => 'مزاد الاختبار',
            'date_start_at' => now(),
            'date_end_at' => now()->addDay(),
            'image' => json_encode([]),
        ]);

        $html = (new VideoController())->edit($liveVideo->id)->render();

        $this->assertStringContainsString('md-page-icon', $html);
        $this->assertStringContainsString('مزاد الاختبار', $html);
        $this->assertEquals(1, substr_count($html, 'class="card"'));
    }

    public function test_create_page_still_lists_active_cities()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());
        $city = City::create(['name' => json_encode(['ar' => 'الرياض', 'en' => 'Riyadh']), 'is_active' => 1]);

        $html = (new VideoController())->create()->render();

        $this->assertStringContainsString('value="' . $city->id . '"', $html);
    }

    /**
     * Per explicit follow-up request: the "schedule" fields (date/time)
     * moved up to sit right after the auction-details fields, and "terms &
     * conditions" moved down to be the very last section in the form, now
     * spanning the form's full width (col-12) instead of half (col-lg-6).
     */
    public function test_schedule_section_comes_before_terms_and_conditions_which_is_now_last_and_full_width()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new VideoController())->create()->render();

        $schedulePos = strpos($html, 'name="date_start_at"');
        $bannerPos = strpos($html, 'name="image[]"');
        $feesPos = strpos($html, 'name="tax_amount"');
        $termsPos = strpos($html, 'name="terms_conditions_ar"');
        $saveButtonPos = strpos($html, 'name="action" value="save"');

        $this->assertNotFalse($schedulePos);
        $this->assertNotFalse($termsPos);

        // Schedule now comes before banner/fees, and terms & conditions is
        // the last field section (right before the submit buttons).
        $this->assertLessThan($bannerPos, $schedulePos);
        $this->assertLessThan($termsPos, $bannerPos);
        $this->assertLessThan($termsPos, $feesPos);
        $this->assertLessThan($saveButtonPos, $termsPos);

        // Terms & conditions is now full width, not the old half-width column.
        $this->assertMatchesRegularExpression(
            '/class="col-12 form-group mb-3">\s*<label[^>]*for="terms_conditions_ar"/',
            $html
        );
    }

    /**
     * Per explicit follow-up request: in the optional fees row, the
     * short-labeled fields (tax/commission %) and the long-labeled one
     * ("service fee — includes hosting and any extra charges") no longer
     * leave their inputs misaligned (one field sitting lower than the
     * others) — every label in that row reserves the same two-line height
     * via the new md-label-2-lines utility class.
     */
    public function test_fee_row_labels_share_the_two_line_alignment_class()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new VideoController())->create()->render();

        $this->assertMatchesRegularExpression('/for="tax_amount"[^>]*class="form-label md-label-2-lines"|class="form-label md-label-2-lines"[^>]*for="tax_amount"/', $html);
        foreach (['tax_amount', 'commission_amount', 'service_fee', 'commission_payer'] as $field) {
            $this->assertStringContainsString("for=\"{$field}\"", $html);
        }
        $this->assertEquals(4, substr_count($html, 'md-label-2-lines'));
    }
}
