<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\NotificationsController;
use App\Models\Admin;
use App\Models\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * /admin/notifications/create had its "title" field labeled with the
 * generic shared translation key "title" — which, in Arabic, is
 * "اسم المزاد (إنجليزي)" ("Auction Name (English)"), a label written for
 * the (separate) auction form's hidden English title field. Since
 * TranslationHelper resolves translations by a shared, lowercased key
 * regardless of which form calls it, the notification form's label
 * collided with the auction form's and showed the wrong text/wrong
 * language hint entirely. Fixed by giving the notification title field its
 * own dedicated "notification_title" key instead of touching the shared
 * "title" key (which the auction and product forms still rely on).
 */
class NotificationTitleLabelTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(array $permissions): Admin
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

    public function test_create_page_labels_the_field_as_notification_title_not_auction_name()
    {
        Auth::guard('admin')->setUser($this->createAdmin(['add notification']));
        view()->share('errors', new ViewErrorBag());

        $html = (new NotificationsController())->create()->render();

        $this->assertStringContainsString(TranslationHelper::translate('notification_title'), $html);
        $this->assertStringNotContainsString(TranslationHelper::translate('title'), $html);
    }

    public function test_notification_title_translation_has_no_english_word_and_no_auction_wording()
    {
        $arabicLabel = TranslationHelper::translate('notification_title', 'ar');

        $this->assertStringNotContainsString('إنجليزي', $arabicLabel);
        $this->assertStringNotContainsString('المزاد', $arabicLabel);
        $this->assertEquals('اسم الاشعار', $arabicLabel);
    }

    public function test_edit_page_labels_the_field_as_notification_title_not_auction_name()
    {
        Auth::guard('admin')->setUser($this->createAdmin(['edit notification']));
        view()->share('errors', new ViewErrorBag());

        $notification = Notification::create([
            'title' => 'Test notification',
            'description' => 'Body',
        ]);

        $html = (new NotificationsController())->view($notification->id)->render();

        $this->assertStringContainsString(TranslationHelper::translate('notification_title'), $html);
    }
}
