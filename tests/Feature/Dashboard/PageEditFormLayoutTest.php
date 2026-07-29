<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\PageController;
use App\Models\Admin;
use App\Models\Page;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Regression guard: the "About" page edit form's Arabic description textarea
 * only rendered 2 rows by default (LaravelCollective's Form::textarea()
 * default), making it look tiny/cramped next to the name input in the same
 * row, even though both sit in equal col-lg-6 columns. Explicitly sizing it
 * (rows=6) gives it a usable height that visually matches its column width.
 */
class PageEditFormLayoutTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdminWithEditPagePermission(): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        $permission = Permission::firstOrCreate([
            'name' => 'edit page',
            'guard_name' => 'admin',
        ]);
        $admin->givePermissionTo($permission);

        return $admin;
    }

    public function test_description_textarea_has_an_explicit_usable_row_count()
    {
        Auth::guard('admin')->setUser($this->createAdminWithEditPagePermission());
        view()->share('errors', new ViewErrorBag());

        $page = Page::create([
            'name' => json_encode(['ar' => 'من نحن']),
            'description' => json_encode(['ar' => 'مزادات هي منصة إلكترونية متخصصة في مزادات المواشي']),
            'image' => '',
        ]);

        $html = (new PageController())->edit($page->id)->render();

        $this->assertMatchesRegularExpression(
            '/<textarea[^>]*rows="6"[^>]*name="description\[ar\]"/',
            $html
        );
    }

    public function test_name_and_description_fields_sit_in_equal_width_columns()
    {
        Auth::guard('admin')->setUser($this->createAdminWithEditPagePermission());
        view()->share('errors', new ViewErrorBag());

        $page = Page::create([
            'name' => json_encode(['ar' => 'من نحن']),
            'description' => json_encode(['ar' => 'وصف']),
            'image' => '',
        ]);

        $html = (new PageController())->edit($page->id)->render();

        $this->assertMatchesRegularExpression('/<div class="col-lg-6">\s*<div class="form-group">\s*<label[^>]*for="name\[ar\]"/s', $html);
        $this->assertMatchesRegularExpression('/col-lg-6 form-group[^>]*>\s*<label[^>]*for="description\[ar\]"/s', $html);
    }

    /**
     * Regression guard: Bootstrap's ".row" is a flex container with the
     * default "align-items: stretch", so once the description textarea grew
     * taller than the single-line name input (after the height/rows fix),
     * both columns stretched to match it — leaving a large empty gap below
     * the short input field. "align-items-start" lets each column size to
     * its own content instead of matching its tallest sibling.
     */
    public function test_form_row_does_not_stretch_columns_to_match_the_tallest_field()
    {
        Auth::guard('admin')->setUser($this->createAdminWithEditPagePermission());
        view()->share('errors', new ViewErrorBag());

        $page = Page::create([
            'name' => json_encode(['ar' => 'من نحن']),
            'description' => json_encode(['ar' => 'وصف']),
            'image' => '',
        ]);

        $html = (new PageController())->edit($page->id)->render();

        $this->assertStringContainsString('row align-items-start', $html);
    }

    /**
     * Regression guard: the name input and PNG image field used to be two
     * separate top-level columns in the same flex "row" as the description
     * column. Since 6+6 already fills the row, the image field wrapped onto
     * a second flex line — and that second line only starts below the
     * *tallest* column of the first line (the description textarea), not
     * below the actual (short) name input directly above it. That left a
     * large empty gap between the name input and the image field whenever
     * the description grew past a couple of lines. Nesting the image field
     * inside the name field's own column makes it stack immediately after
     * the name input regardless of the description column's height.
     */
    public function test_image_field_is_nested_inside_the_name_column_not_a_sibling_in_the_row()
    {
        Auth::guard('admin')->setUser($this->createAdminWithEditPagePermission());
        view()->share('errors', new ViewErrorBag());

        $page = Page::create([
            'name' => json_encode(['ar' => 'من نحن']),
            'description' => json_encode(['ar' => 'وصف']),
            'image' => '',
        ]);

        $html = (new PageController())->edit($page->id)->render();

        $nameColumnStart = strpos($html, '<div class="col-lg-6">');
        $descriptionColumnStart = strpos($html, 'col-lg-6 form-group');
        $imageLabelPos = strpos($html, 'for="image"');

        $this->assertNotFalse($nameColumnStart);
        $this->assertNotFalse($descriptionColumnStart);
        $this->assertNotFalse($imageLabelPos);
        $this->assertGreaterThan($nameColumnStart, $imageLabelPos);
        $this->assertLessThan($descriptionColumnStart, $imageLabelPos);
    }
}
