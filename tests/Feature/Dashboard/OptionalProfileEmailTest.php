<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\Dashboard\AdminProfileController;
use App\Http\Requests\Dashboard\Admin\UpdateProfileRequest;
use App\Models\Admin;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OptionalProfileEmailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'user_name', 'email', 'phone', 'image', 'user_id'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
            $table->softDeletes();
        });
        Storage::fake('public');
        app()->setLocale('ar');
    }

    private function admin(?string $email = null): Admin
    {
        $admin = Admin::create(['name' => 'Subscriber', 'user_name' => 'subscriber',
            'phone' => '+966512345678', 'email' => $email]);
        Auth::guard('admin')->setUser($admin);
        return $admin;
    }

    private function request(array $values, bool $image = true): UpdateProfileRequest
    {
        $request = UpdateProfileRequest::create('/ar/admin/update-profile', 'POST',
            array_merge(['name' => 'Subscriber', 'phone_local' => '512345678'], $values), [],
            $image ? ['image' => UploadedFile::fake()->image('avatar.png')] : []);
        $request->setContainer(app())->setRedirector(app('redirect'))->validateResolved();
        return $request;
    }

    public static function emails(): array
    {
        return [[[]], [['email' => null]], [['email' => '']], [['email' => 'subscriber@example.com']]];
    }

    /** @dataProvider emails */
    public function test_image_upload_with_optional_email_preserves_other_fields(array $values): void
    {
        $admin = $this->admin();
        (new AdminProfileController())->update_profile($this->request($values));
        $admin->refresh();
        Storage::disk('public')->assertExists($admin->image);
        $this->assertSame('Subscriber', $admin->name);
        $this->assertSame('subscriber', $admin->user_name);
        $this->assertSame('+966512345678', $admin->phone);
        $this->assertEquals($values['email'] ?? null, $admin->email);
        $html = view('dashboard.partials.avatar', ['path' => $admin->image])->render();
        $this->assertStringContainsString(asset('storage/'.$admin->image), $html);
        $sidebarImage = view('dashboard.partials.subscriber-brand-image')->render();
        $this->assertStringContainsString(asset('storage/'.$admin->image), $sidebarImage);
    }

    public function test_omitted_email_and_image_preserve_existing_values(): void
    {
        $admin = $this->admin('subscriber@example.com');
        Storage::disk('public')->put('admins/existing.png', 'existing');
        $admin->update(['image' => 'admins/existing.png']);
        (new AdminProfileController())->update_profile($this->request([], false));
        $admin->refresh();
        $this->assertSame('subscriber@example.com', $admin->email);
        $this->assertSame('admins/existing.png', $admin->image);
        Storage::disk('public')->assertExists($admin->image);
    }

    public function test_unchanged_email_is_allowed(): void
    {
        $this->admin('subscriber@example.com');
        $this->request(['email' => 'subscriber@example.com'], false);
        $this->addToAssertionCount(1);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->admin();
        $this->expectException(ValidationException::class);
        $this->request(['email' => 'invalid']);
    }

    public function test_another_accounts_email_is_rejected(): void
    {
        $this->admin();
        Admin::create(['email' => 'taken@example.com']);
        $this->expectException(ValidationException::class);
        $this->request(['email' => 'taken@example.com']);
    }
}
