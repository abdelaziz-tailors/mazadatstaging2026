<?php

namespace Tests\Feature\Bugfixes;

use App\Http\Controllers\Dashboard\ProductController;
use App\Models\Admin;
use Illuminate\Support\Facades\{Auth, View};
use Illuminate\Support\{MessageBag, ViewErrorBag};
use Tests\Support\AuctionBugfixTestCase;

class FinanceNavigationTest extends AuctionBugfixTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once database_path('migrations/New folder/2022_10_16_092804_create_permission_tables.php');
        (new \CreatePermissionTables())->up();
    }

    private function allowNavigation(string $type): void
    {
        $admin = new class extends Admin {
            public function can($abilities, $arguments = []) { return true; }
            public function canAny($abilities, $arguments = []) { return true; }
        };
        Auth::guard('admin')->setUser($admin->forceFill(['id' => 1, 'name' => 'Fixture admin', 'type' => $type, 'role' => 'admin']));
    }

    public function test_both_sidebars_render_finance_invoice_icon_supported_by_local_font(): void
    {
        foreach (['sidebar' => 'admin', 'sidebar_subscriber' => 'partner'] as $view => $type) {
            $this->allowNavigation($type);
            $html = view('dashboard.layouts.'.$view)->render();
            $dom = new \DOMDocument(); @$dom->loadHTML($html);
            $icons = (new \DOMXPath($dom))->query('//a[contains(@href,"/partner-finance/invoices")]/i');
            $this->assertSame(1, $icons->length);
            $this->assertStringContainsString('fa-file-invoice-dollar', $icons->item(0)->getAttribute('class'));
        }
        $this->assertStringContainsString('.fa-file-invoice-dollar::before', file_get_contents(public_path('dashboard/fonts/fontawesome/css/all.css')));
    }

    public function test_item_validation_renders_one_summary_and_keeps_inline_errors(): void
    {
        $owner = $this->user(['user_type' => 'vendor']); $admin = $this->admin($owner);
        $auction = $this->auction($owner, ['admin_id' => $admin->id]);
        $data = app(ProductController::class)->create($auction->id)->getData();
        $this->allowNavigation('admin');
        // Header data is unrelated to the form; avoid database-backed view composers.
        View::getFacadeRoot()->getDispatcher()->forget('composing: dashboard.layouts.app');
        $data['errors'] = (new ViewErrorBag())->put('default', new MessageBag(['bidding' => 'Bid increment must be nonnegative.']));
        $html = view('dashboard.pages.products.create', $data)->render();
        $dom = new \DOMDocument(); @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//div[contains(@class,"alert-danger")]')->length);
        $this->assertSame(1, $xpath->query('//input[@name="bidding" and contains(@class,"is-invalid")]')->length);
        $this->assertStringContainsString('data-error-field="bidding"', $html);
    }
}
