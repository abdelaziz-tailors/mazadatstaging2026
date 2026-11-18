<?php

namespace Tests\Feature\Dashboard;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubscriberBrandImageTest extends TestCase
{
    public function test_brand_image_tracks_the_current_account_and_updated_profile_path(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('admins/first.png', 'image');
        Storage::disk('public')->put('admins/updated.png', 'image');
        Storage::disk('public')->put('admins/second.png', 'image');
        $subscriber = new Admin(['name' => 'First', 'type' => 'partner', 'image' => 'admins/first.png']);
        Auth::guard('admin')->setUser($subscriber);

        $this->assertStringContainsString(asset('storage/admins/first.png'), $this->renderBrand());
        $subscriber->image = 'admins/updated.png';
        $updated = $this->renderBrand();
        $this->assertStringContainsString(asset('storage/admins/updated.png'), $updated);
        $this->assertStringNotContainsString('admins/first.png', $updated);

        Auth::guard('admin')->setUser(new Admin(['name' => 'Second', 'image' => '/storage/admins/second.png']));
        $second = $this->renderBrand();
        $this->assertStringContainsString(asset('storage/admins/second.png'), $second);
        $this->assertStringNotContainsString('admins/updated.png', $second);
    }

    public function test_missing_images_use_a_default_and_load_errors_have_a_fallback(): void
    {
        Storage::fake('public');
        foreach ([null, '', 'admins/default.png', 'admins/missing.png'] as $path) {
            Auth::guard('admin')->setUser(new Admin(['name' => 'Subscriber', 'image' => $path]));
            $html = $this->renderBrand();
            $this->assertStringContainsString('src="data:image/svg+xml;base64,', $html);
            $this->assertStringContainsString('this.onerror=null; this.src=this.dataset.defaultAvatar;', $html);
            $this->assertStringContainsString('object-fit: cover', $html);
            $this->assertStringContainsString('border-radius: 50%', $html);
        }
    }

    public function test_compact_brand_keeps_its_existing_dimensions(): void
    {
        Auth::guard('admin')->setUser(new Admin(['name' => 'Subscriber']));
        $this->assertStringContainsString('width="30" height="30"', $this->renderBrand(['compact' => true]));
        $this->assertStringContainsString('width="48" height="48"', $this->renderBrand());
    }

    private function renderBrand(array $data = []): string
    {
        return view('dashboard.partials.subscriber-brand-image', $data)->render();
    }
}
