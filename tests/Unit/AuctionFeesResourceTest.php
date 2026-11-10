<?php

namespace Tests\Unit;

use App\Http\Resources\User\AuctionFeesResource;
use App\Models\LiveVideo;
use App\Models\Video;
use App\Models\User\User;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class AuctionFeesResourceTest extends TestCase
{
    /** @dataProvider viewers */
    public function test_percentage_visibility_depends_on_payer_and_auction_relationship($payer, $viewerId, $consignor, $visible): void
    {
        $auction = $this->getMockBuilder(LiveVideo::class)->onlyMethods(['video_items'])->getMock();
        $auction->forceFill(['user_id' => 10, 'commission_payer' => $payer,
            'commission_amount' => 5, 'service_fee' => 20]);
        $query = new class($consignor) {
            private $exists;
            public function __construct($exists) { $this->exists = $exists; }
            public function where($column, $id) { return $this; }
            public function exists() { return $this->exists; }
        };
        $auction->method('video_items')->willReturn($query);
        $request = Request::create('/');
        $user = $viewerId ? (new User())->forceFill(['id' => $viewerId]) : null;
        $request->setUserResolver(function ($guard) use ($user) {
            self::assertSame('api', $guard);
            return $user;
        });

        $fees = (new AuctionFeesResource($auction))->toArray($request);
        // Match the original fee contract exactly, including key order and value types.
        self::assertSame([
            'commission_amount' => $visible ? 5 : null,
            'commission_payer' => $visible ? $payer : null,
            'service_fee' => 20,
        ], $fees);
    }

    public static function viewers(): array
    {
        return [
            ['buyer', 20, false, true],
            ['buyer', 10, false, true],
            ['seller', 10, false, true],
            ['seller', 20, false, false],
            ['seller', 30, true, true],
            ['buyer', 30, true, true],
            ['buyer', null, false, false],
            ['seller', null, false, false],
            [null, 20, false, false],
            [null, 10, false, false],
            ['invalid', 30, true, false],
        ];
    }

    public function test_ordinary_video_does_not_query_auction_relationships_or_expose_fees(): void
    {
        $video = (new Video())->forceFill(['user_id' => 10, 'commission_payer' => 'seller']);
        $request = Request::create('/');
        $request->setUserResolver(fn () => (new User())->forceFill(['id' => 20]));

        self::assertSame(['commission_amount' => null, 'commission_payer' => null, 'service_fee' => null],
            (new AuctionFeesResource($video))->toArray($request));
    }

    public function test_visible_zero_percentage_is_not_omitted(): void
    {
        $auction = (new LiveVideo())->forceFill(['user_id' => 10, 'commission_payer' => 'buyer',
            'commission_amount' => 0, 'service_fee' => 20]);
        $request = Request::create('/');
        $request->setUserResolver(fn () => (new User())->forceFill(['id' => 20]));

        self::assertSame(['commission_amount' => 0, 'commission_payer' => 'buyer', 'service_fee' => 20],
            (new AuctionFeesResource($auction))->toArray($request));
    }
}
