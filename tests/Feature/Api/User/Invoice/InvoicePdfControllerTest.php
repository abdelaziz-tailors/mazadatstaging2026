<?php

namespace Tests\Feature\Api\User\Invoice;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

class InvoicePdfControllerTest extends TestCase
{
    use DatabaseTransactions;

    private const API_KEY = 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM';

    private function headers(): array
    {
        return [
            'x-api-key' => self::API_KEY,
            'Accept-Language' => 'ar',
        ];
    }

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test Buyer',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ], $overrides));
    }

    private function createOrder(User $buyer, array $overrides = []): Order
    {
        $liveVideo = LiveVideo::create([
            'title' => 'Sheep auction',
            'title_ar' => 'مزاد نخبة الحلال',
            'commission_amount' => 5,
            'commission_payer' => 'buyer',
        ]);

        $liveVideoItem = LiveVideoItem::create([
            'live_video_id' => $liveVideo->id,
            'title' => 'Najdi sheep',
            'title_ar' => 'خروف نجدي',
            'finished_price' => 3100,
        ]);

        $order = Order::create(array_merge([
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'subtotal' => 3100,
            'tax_percent' => 15,
            'tax_value' => 21.45,
            'commission_percent' => 3,
            'commission_payer' => 'buyer',
            'commission_value' => 93,
            'service_fee_per_item' => 50,
            'service_fee_total' => 50,
            'total' => 3264.45,
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ], $overrides));

        OrderItem::create([
            'order_id' => $order->id,
            'live_video_item_id' => $liveVideoItem->id,
            'finished_price' => 3100,
        ]);

        return $order;
    }

    public function test_downloads_pdf_for_the_owning_buyer()
    {
        $buyer = $this->createUser();
        $order = $this->createOrder($buyer);
        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())
            ->get("/api/user/auction/user-invoice/{$order->id}/pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString(
            'INV-' . $order->id,
            $response->headers->get('content-disposition')
        );
        $this->assertNotEmpty($response->getContent());
    }

    public function test_returns_failure_when_order_belongs_to_a_different_buyer()
    {
        $buyer = $this->createUser();
        $otherBuyer = $this->createUser();
        $order = $this->createOrder($otherBuyer);
        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())
            ->get("/api/user/auction/user-invoice/{$order->id}/pdf");

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_returns_failure_when_order_does_not_exist()
    {
        $buyer = $this->createUser();
        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())
            ->get('/api/user/auction/user-invoice/999999/pdf');

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_requires_authentication()
    {
        $buyer = $this->createUser();
        $order = $this->createOrder($buyer);

        $response = $this->withHeaders($this->headers())
            ->getJson("/api/user/auction/user-invoice/{$order->id}/pdf");

        $response->assertStatus(401);
    }
}
