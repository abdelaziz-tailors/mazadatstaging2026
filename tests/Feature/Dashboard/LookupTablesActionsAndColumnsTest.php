<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\AgeController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\ColorController;
use App\Http\Controllers\Dashboard\NotificationsController;
use App\Http\Controllers\Dashboard\SliderController;
use App\Models\Admin;
use App\Models\Age;
use App\Models\Category;
use App\Models\Color;
use App\Models\Notification;
use App\Models\Slider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Visual/data-completeness pass over the small lookup-table admin pages
 * (ages, colors, categories, sliders, notifications): each previously had
 * no search placeholder and a generic "fa-cogs" dropdown-only actions
 * column. This restyles actions to the round icon-button component
 * (edit + kebab-delete, matching users/buyer — notifications gets
 * view + kebab since it has no editable fields), adds a descriptive
 * search placeholder, and surfaces real additional data already on the
 * underlying records (created_at everywhere, a color swatch on colors,
 * and title/description/created_at on notifications).
 */
class LookupTablesActionsAndColumnsTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdminWithPermissions(array $permissions = []): Admin
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ]);

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    private function rowFor(string $controllerClass, string $routeName, $model): array
    {
        $request = Request::create("/admin/{$routeName}/getData", 'POST', ['draw' => 1, 'start' => 0, 'length' => 50]);
        app()->instance('request', $request);

        $response = (new $controllerClass())->get_data($request);
        $rows = collect(json_decode($response->getContent(), true)['data']);

        return $rows->firstWhere('id', $model->id);
    }

    // ---- Ages ----

    public function test_ages_get_data_includes_created_at()
    {
        $admin = $this->createAdminWithPermissions();
        Auth::guard('admin')->setUser($admin);
        $age = Age::create(['name' => json_encode(['en' => 'Young', 'ar' => 'صغير']), 'admin_id' => $admin->id, 'is_active' => true]);

        $row = $this->rowFor(AgeController::class, 'ages', $age);

        $this->assertEquals($age->created_at->format('Y-m-d'), $row['created_at']);
    }

    public function test_ages_actions_are_icon_style_with_edit_and_kebab_delete()
    {
        $admin = $this->createAdminWithPermissions();
        Auth::guard('admin')->setUser($admin);
        $age = Age::create(['name' => json_encode(['en' => 'Young', 'ar' => 'صغير']), 'admin_id' => $admin->id, 'is_active' => true]);

        $row = $this->rowFor(AgeController::class, 'ages', $age);

        $this->assertStringContainsString('md-icon-btn', $row['action']);
        $this->assertStringContainsString(route('admin.ages.edit', $age->id), $row['action']);
        $this->assertStringContainsString('fa-ellipsis-vertical', $row['action']);
        $this->assertStringNotContainsString('fa-cogs', $row['action']);
    }

    public function test_ages_index_page_has_a_descriptive_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new AgeController())->index()->render();

        $this->assertStringContainsString('searchPlaceholder', $html);
        $this->assertStringContainsString(TranslationHelper::translate('search_ages_placeholder'), $html);
    }

    // ---- Colors ----

    public function test_colors_get_data_includes_a_color_swatch_and_created_at()
    {
        $admin = $this->createAdminWithPermissions();
        Auth::guard('admin')->setUser($admin);
        $color = Color::create(['name' => json_encode(['en' => 'Red', 'ar' => 'أحمر']), 'admin_id' => $admin->id, 'color' => '#ff0000', 'is_active' => true]);

        $row = $this->rowFor(ColorController::class, 'colors', $color);

        $this->assertStringContainsString('#ff0000', $row['color']);
        $this->assertEquals($color->created_at->format('Y-m-d'), $row['created_at']);
    }

    public function test_colors_actions_are_icon_style_with_edit_and_kebab_delete()
    {
        $admin = $this->createAdminWithPermissions();
        Auth::guard('admin')->setUser($admin);
        $color = Color::create(['name' => json_encode(['en' => 'Red', 'ar' => 'أحمر']), 'admin_id' => $admin->id, 'color' => '#ff0000', 'is_active' => true]);

        $row = $this->rowFor(ColorController::class, 'colors', $color);

        $this->assertStringContainsString('md-icon-btn', $row['action']);
        $this->assertStringContainsString(route('admin.colors.edit', $color->id), $row['action']);
        $this->assertStringNotContainsString('fa-cogs', $row['action']);
    }

    public function test_colors_index_page_has_a_descriptive_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new ColorController())->index()->render();

        $this->assertStringContainsString(TranslationHelper::translate('search_colors_placeholder'), $html);
    }

    // ---- Categories ----

    public function test_categories_get_data_includes_created_at()
    {
        $admin = $this->createAdminWithPermissions();
        Auth::guard('admin')->setUser($admin);
        $category = Category::create(['name' => json_encode(['en' => 'Sheep', 'ar' => 'غنم']), 'admin_id' => $admin->id, 'is_active' => true]);

        $row = $this->rowFor(CategoryController::class, 'categories', $category);

        $this->assertEquals($category->created_at->format('Y-m-d'), $row['created_at']);
    }

    public function test_categories_actions_are_icon_style_with_edit_and_kebab_delete()
    {
        $admin = $this->createAdminWithPermissions();
        Auth::guard('admin')->setUser($admin);
        $category = Category::create(['name' => json_encode(['en' => 'Sheep', 'ar' => 'غنم']), 'admin_id' => $admin->id, 'is_active' => true]);

        $row = $this->rowFor(CategoryController::class, 'categories', $category);

        $this->assertStringContainsString('md-icon-btn', $row['action']);
        $this->assertStringContainsString(route('admin.categories.edit', $category->id), $row['action']);
        $this->assertStringNotContainsString('fa-cogs', $row['action']);
    }

    public function test_categories_index_page_has_a_descriptive_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new CategoryController())->index()->render();

        $this->assertStringContainsString(TranslationHelper::translate('search_categories_placeholder'), $html);
    }

    // ---- Sliders ----

    public function test_sliders_get_data_includes_created_at()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        $slider = Slider::create(['image' => 'sliders/test.png', 'link' => 'https://example.com', 'position' => 1, 'is_active' => true]);

        $row = $this->rowFor(SliderController::class, 'sliders', $slider);

        $this->assertEquals($slider->created_at->format('Y-m-d'), $row['created_at']);
    }

    public function test_sliders_actions_are_icon_style_with_edit_and_kebab_delete()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        $slider = Slider::create(['image' => 'sliders/test.png', 'link' => 'https://example.com', 'position' => 1, 'is_active' => true]);

        $row = $this->rowFor(SliderController::class, 'sliders', $slider);

        $this->assertStringContainsString('md-icon-btn', $row['action']);
        $this->assertStringContainsString(route('admin.sliders.edit', $slider->id), $row['action']);
        $this->assertStringNotContainsString('fa-cogs', $row['action']);
    }

    public function test_sliders_index_page_has_a_descriptive_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions());
        view()->share('errors', new ViewErrorBag());

        $html = (new SliderController())->index()->render();

        $this->assertStringContainsString(TranslationHelper::translate('search_sliders_placeholder'), $html);
    }

    // ---- Notifications ----

    public function test_notifications_get_data_includes_description_and_created_at()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions(['view notifications', 'edit notification', 'delete notification']));
        $notification = Notification::create(['title' => 'Test Title', 'description' => str_repeat('a', 100)]);

        $row = $this->rowFor(NotificationsController::class, 'notifications', $notification);

        $this->assertEquals($notification->created_at->format('Y-m-d'), $row['created_at']);
        $this->assertLessThanOrEqual(64, strlen($row['description']));
    }

    public function test_notifications_actions_have_a_view_icon_and_kebab_delete_not_a_generic_cogs_button()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions(['view notifications', 'edit notification', 'delete notification']));
        $notification = Notification::create(['title' => 'Test Title', 'description' => 'desc']);

        $row = $this->rowFor(NotificationsController::class, 'notifications', $notification);

        $this->assertStringContainsString('md-icon-btn', $row['action']);
        $this->assertStringContainsString(route('admin.notifications.view', $notification->id), $row['action']);
        $this->assertStringContainsString('fa-ellipsis-vertical', $row['action']);
        $this->assertStringNotContainsString('fa-cogs', $row['action']);
    }

    public function test_notifications_index_page_has_a_descriptive_search_placeholder()
    {
        Auth::guard('admin')->setUser($this->createAdminWithPermissions(['view notifications']));
        view()->share('errors', new ViewErrorBag());

        $html = (new NotificationsController())->index()->render();

        $this->assertStringContainsString(TranslationHelper::translate('search_notifications_placeholder'), $html);
    }
}
