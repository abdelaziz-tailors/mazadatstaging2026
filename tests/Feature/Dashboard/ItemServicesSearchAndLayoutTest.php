<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\ItemServiceController;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * The item-services list (/admin/item-services) had no "language" block at
 * all (raw DataTables English defaults, no placeholder) and no custom "dom"
 * layout, so its search box fell back to DataTables' default two-column
 * Bootstrap row instead of being pinned to the far edge like every other
 * table — the same bug found and fixed on seller-submissions.
 */
class ItemServicesSearchAndLayoutTest extends TestCase
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

    public function test_index_page_has_a_descriptive_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new ItemServiceController())->index()->render();

        $this->assertStringContainsString('searchPlaceholder', $html);
        $this->assertStringContainsString(TranslationHelper::translate('search_item_services_placeholder'), $html);
        $this->assertStringContainsString('"search":', $html);
    }

    public function test_index_page_has_the_standard_toolbar_dom_layout_and_padding()
    {
        Auth::guard('admin')->setUser($this->createAdmin());
        view()->share('errors', new ViewErrorBag());

        $html = (new ItemServiceController())->index()->render();

        $this->assertStringContainsString(
            'd-flex flex-wrap justify-content-between align-items-center mb-3 px-2',
            $html
        );
        $this->assertStringContainsString('d-flex justify-content-between px-2', $html);
    }
}
