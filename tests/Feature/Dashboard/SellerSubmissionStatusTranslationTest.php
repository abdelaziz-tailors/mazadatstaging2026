<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\SellerSubmissionController;
use App\Models\Admin;
use App\Models\SellerSubmission;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

/**
 * Regression guard: the seller-submissions table's status column rendered
 * the raw English DB value ("pending", "approved", ...) verbatim regardless
 * of the dashboard's current locale. Translating it via TranslationHelper
 * (same convention used everywhere else in this app) makes it follow
 * Accept-Language/admin locale like every other label on the page, without
 * changing the underlying `status` column value or its badge color logic.
 */
class SellerSubmissionStatusTranslationTest extends TestCase
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

    private function createSubmission(string $status): SellerSubmission
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

    private function statusBadgeHtml(string $status): string
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        $this->createSubmission($status);

        $request = Request::create('/admin/seller-submissions/getData', 'POST', ['draw' => 1, 'start' => 0, 'length' => 50]);
        app()->instance('request', $request);

        $response = (new SellerSubmissionController())->get_data($request);
        $rows = collect(json_decode($response->getContent(), true)['data']);
        $row = $rows->firstWhere('status', $status);

        return $row['status_badge'];
    }

    public function test_pending_status_badge_is_translated_to_arabic()
    {
        App::setLocale('ar');

        $html = $this->statusBadgeHtml('pending');

        $this->assertStringContainsString(TranslationHelper::translate('pending'), $html);
        $this->assertStringNotContainsString('>pending<', $html);
    }

    public function test_needs_edit_status_badge_is_translated_to_arabic_not_left_in_english()
    {
        App::setLocale('ar');

        $html = $this->statusBadgeHtml('needs edit');

        $this->assertStringContainsString('يحتاج تعديل', $html);
        $this->assertStringNotContainsString('>needs edit<', $html);
    }

    public function test_approved_status_badge_uses_success_color_and_translated_text()
    {
        App::setLocale('en');

        $html = $this->statusBadgeHtml('approved');

        $this->assertStringContainsString('bg-success', $html);
        $this->assertStringContainsString('Approved', $html);
    }
}
