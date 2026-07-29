<?php

namespace Tests\Feature\Console;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Notification;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SendUpcomingAuctionRemindersTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Seller',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'seller',
            'gender' => 'male',
        ], $overrides));
    }

    public function test_notifies_sellers_of_an_auction_starting_within_the_next_hour()
    {
        $seller = $this->createUser();
        $startsAt = now()->addMinutes(30);

        $liveVideo = LiveVideo::create([
            'title' => 'Soon auction',
            'date_start_at' => $startsAt->toDateString(),
            'time_start_at' => $startsAt->toTimeString(),
        ]);

        LiveVideoItem::create([
            'live_video_id' => $liveVideo->id,
            'seller_id' => $seller->id,
            'finished_price' => 100,
        ]);

        $this->artisan('notifications:upcoming-auction-reminders')->assertExitCode(0);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $seller->id,
            'type' => 'upcoming_auction',
        ]);
        $this->assertNotNull($liveVideo->fresh()->upcoming_reminder_sent_at);
    }

    public function test_does_not_notify_twice_for_the_same_auction()
    {
        $seller = $this->createUser();
        $startsAt = now()->addMinutes(30);

        $liveVideo = LiveVideo::create([
            'title' => 'Soon auction',
            'date_start_at' => $startsAt->toDateString(),
            'time_start_at' => $startsAt->toTimeString(),
        ]);

        LiveVideoItem::create([
            'live_video_id' => $liveVideo->id,
            'seller_id' => $seller->id,
            'finished_price' => 100,
        ]);

        $this->artisan('notifications:upcoming-auction-reminders');
        $this->artisan('notifications:upcoming-auction-reminders');

        $this->assertSame(
            1,
            Notification::where('user_id', $seller->id)->where('type', 'upcoming_auction')->count()
        );
    }

    public function test_ignores_auctions_starting_more_than_an_hour_from_now()
    {
        $seller = $this->createUser();
        $startsAt = now()->addHours(3);

        $liveVideo = LiveVideo::create([
            'title' => 'Far auction',
            'date_start_at' => $startsAt->toDateString(),
            'time_start_at' => $startsAt->toTimeString(),
        ]);

        LiveVideoItem::create([
            'live_video_id' => $liveVideo->id,
            'seller_id' => $seller->id,
            'finished_price' => 100,
        ]);

        $this->artisan('notifications:upcoming-auction-reminders');

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $seller->id,
            'type' => 'upcoming_auction',
        ]);
        $this->assertNull($liveVideo->fresh()->upcoming_reminder_sent_at);
    }

    public function test_ignores_auctions_that_already_started()
    {
        $seller = $this->createUser();
        $startsAt = now()->addMinutes(30);

        $liveVideo = LiveVideo::create([
            'title' => 'Live now',
            'status' => 'start',
            'date_start_at' => $startsAt->toDateString(),
            'time_start_at' => $startsAt->toTimeString(),
        ]);

        LiveVideoItem::create([
            'live_video_id' => $liveVideo->id,
            'seller_id' => $seller->id,
            'finished_price' => 100,
        ]);

        $this->artisan('notifications:upcoming-auction-reminders');

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $seller->id,
            'type' => 'upcoming_auction',
        ]);
    }

    public function test_notifies_the_organizer_who_created_the_auction()
    {
        $organizer = $this->createUser(['user_type' => 'vendor']);
        $startsAt = now()->addMinutes(30);

        $liveVideo = LiveVideo::create([
            'title' => 'Soon auction',
            'user_id' => $organizer->id,
            'date_start_at' => $startsAt->toDateString(),
            'time_start_at' => $startsAt->toTimeString(),
        ]);

        $this->artisan('notifications:upcoming-auction-reminders');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $organizer->id,
            'type' => 'upcoming_auction',
        ]);
    }

    public function test_does_not_double_notify_organizer_who_is_also_the_seller()
    {
        $organizer = $this->createUser(['user_type' => 'buyer_vendor']);
        $startsAt = now()->addMinutes(30);

        $liveVideo = LiveVideo::create([
            'title' => 'Soon auction',
            'user_id' => $organizer->id,
            'date_start_at' => $startsAt->toDateString(),
            'time_start_at' => $startsAt->toTimeString(),
        ]);

        LiveVideoItem::create([
            'live_video_id' => $liveVideo->id,
            'seller_id' => $organizer->id,
            'finished_price' => 100,
        ]);

        $this->artisan('notifications:upcoming-auction-reminders');

        $this->assertSame(
            1,
            Notification::where('user_id', $organizer->id)->where('type', 'upcoming_auction')->count()
        );
    }

    public function test_notifies_active_buyers_as_a_broadcast()
    {
        $seller = $this->createUser();
        $activeBuyer = $this->createUser(['user_type' => 'buyer', 'is_active' => 1]);
        $inactiveBuyer = $this->createUser(['user_type' => 'buyer', 'is_active' => 0]);
        $vendor = $this->createUser(['user_type' => 'vendor', 'is_active' => 1]);
        $startsAt = now()->addMinutes(30);

        $liveVideo = LiveVideo::create([
            'title' => 'Soon auction',
            'date_start_at' => $startsAt->toDateString(),
            'time_start_at' => $startsAt->toTimeString(),
        ]);

        LiveVideoItem::create([
            'live_video_id' => $liveVideo->id,
            'seller_id' => $seller->id,
            'finished_price' => 100,
        ]);

        $this->artisan('notifications:upcoming-auction-reminders');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $activeBuyer->id,
            'type' => 'upcoming_auction',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $inactiveBuyer->id,
            'type' => 'upcoming_auction',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $vendor->id,
            'type' => 'upcoming_auction',
        ]);
    }
}
