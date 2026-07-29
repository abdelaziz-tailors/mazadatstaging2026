<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\AdminProfileController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Requests\Dashboard\Admin\UpdateProfileRequest;
use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Two bugs in the admin "my profile" page:
 * 1. The preview `<img>` on the profile-edit page built its src directly
 *    from Storage::disk('public')->url($admin->image) with no existence
 *    check — a null/missing image rendered a broken image icon instead of
 *    a placeholder.
 * 2. The header's own avatar (both the small dropdown-toggle one and the
 *    larger one inside the dropdown menu) was hardcoded to a generic
 *    person icon and never rendered the admin's actual uploaded image at
 *    all, regardless of whether one was set.
 *
 * Both are fixed by reusing the existing "dashboard.partials.avatar"
 * partial (already used elsewhere in the dashboard), which gracefully
 * falls back to a placeholder only when the file is genuinely missing.
 */
class AdminProfileImageTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(array $overrides = []): Admin
    {
        return Admin::create(array_merge([
            'name' => 'Test Admin',
            'email' => 'admin' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'admin',
        ], $overrides));
    }

    public function test_update_profile_stores_the_uploaded_image_and_persists_a_real_path()
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        Auth::guard('admin')->setUser($admin);

        $file = UploadedFile::fake()->image('avatar.png');
        $request = UpdateProfileRequest::create('/admin/update-profile', 'POST', [
            'name' => 'Updated Name',
            'email' => $admin->email,
            'phone' => '0100000000',
        ], [], ['image' => $file]);
        $request->setContainer(app())->validateResolved();

        (new AdminProfileController())->update_profile($request);

        $admin->refresh();
        $this->assertNotNull($admin->image);
        Storage::disk('public')->assertExists($admin->image);
        $this->assertStringStartsWith('admins/', $admin->image);
    }

    public function test_update_profile_deletes_the_old_image_when_replaced()
    {
        Storage::fake('public');
        Storage::disk('public')->put('admins/old.png', 'fake-content');
        $admin = $this->createAdmin(['image' => 'admins/old.png']);
        Auth::guard('admin')->setUser($admin);

        $file = UploadedFile::fake()->image('new-avatar.png');
        $request = UpdateProfileRequest::create('/admin/update-profile', 'POST', [
            'name' => 'Updated Name',
            'email' => $admin->email,
            'phone' => '0100000000',
        ], [], ['image' => $file]);
        $request->setContainer(app())->validateResolved();

        (new AdminProfileController())->update_profile($request);

        Storage::disk('public')->assertMissing('admins/old.png');
    }

    public function test_profile_page_renders_a_real_img_tag_when_the_admin_has_an_image()
    {
        Storage::fake('public');
        Storage::disk('public')->put('admins/me.png', 'fake-content');
        $admin = $this->createAdmin(['image' => 'admins/me.png']);
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new AdminProfileController())->index()->render();

        $this->assertStringContainsString('md-avatar"', $html);
        $this->assertStringContainsString(Storage::disk('public')->url('admins/me.png'), $html);
        $this->assertStringNotContainsString('md-avatar-placeholder', $html);
    }

    public function test_profile_page_renders_a_placeholder_instead_of_a_broken_image_when_no_image_is_set()
    {
        Storage::fake('public');
        $admin = $this->createAdmin(['image' => '']);
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new AdminProfileController())->index()->render();

        $this->assertStringContainsString('md-avatar-placeholder', $html);
        // The old bug: an <img> tag pointed at a non-existent file (broken icon).
        $this->assertStringNotContainsString('<img src="" ', $html);
    }

    /**
     * The real-world root cause: every admin's "image" column defaults to
     * the literal string "admins/default.png" (a NOT NULL column), but no
     * such file actually exists in storage — so any admin who never
     * uploaded their own photo (e.g. the seeded Super Admin account) hit
     * the broken-image bug even though the column wasn't empty/null.
     */
    public function test_profile_page_shows_placeholder_for_the_seeded_default_image_path_that_does_not_exist_on_disk()
    {
        Storage::fake('public');
        $admin = $this->createAdmin(['image' => 'admins/default.png']);
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new AdminProfileController())->index()->render();

        $this->assertStringContainsString('md-avatar-placeholder', $html);
    }

    public function test_header_shows_the_admins_actual_image_when_one_is_set()
    {
        Storage::fake('public');
        Storage::disk('public')->put('admins/header.png', 'fake-content');
        $admin = $this->createAdmin(['image' => 'admins/header.png']);
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString(Storage::disk('public')->url('admins/header.png'), $html);
    }

    public function test_header_shows_the_placeholder_not_a_broken_image_when_the_admin_has_no_image()
    {
        Storage::fake('public');
        $admin = $this->createAdmin(['image' => '']);
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new DashboardController())->index()->render();

        $this->assertStringContainsString('md-avatar-placeholder', $html);
    }
}
