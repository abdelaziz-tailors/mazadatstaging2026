<?php

namespace Tests\Unit;

use App\Http\Resources\User\AuctionFeesResource;
use App\Models\LiveVideo;
use App\Models\User\User;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class AuctionFeesResourceTest extends TestCase
{
    /** @dataProvider viewers */
    public function test_only_the_assigned_party_receives_commission($payer, $viewerId, $consignor, $visible): void
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
        self::assertSame($visible ? 5 : null, $fees['commission_amount']);
        self::assertSame($visible ? $payer : null, $fees['commission_payer']);
        self::assertSame(20, $fees['service_fee']);
    }

    public static function viewers(): array
    {
        return [
            ['buyer', 20, false, true],
            ['buyer', 10, false, false],
            ['seller', 10, false, true],
            ['seller', 20, false, false],
            ['seller', 30, true, true],
            ['buyer', 30, true, false],
            ['buyer', null, false, false],
            ['seller', null, false, false],
            [null, 20, false, false],
        ];
    }
}
