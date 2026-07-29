<?php

namespace Tests\Feature\Api\User;

use App\Models\LiveVideo;
use App\Models\Order;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Bug: once the dashboard marked an order paid + status "confirmed" (or any
 * later stage), it disappeared entirely from the buyer's own order list in
 * the mobile app (GET /api/user/my-cart).
 *
 * Root cause: MyCart() reused Order::scopeActiveCart() (payment_status =
 * unpaid, status != delivered, settled_at is null) — the right scope for
 * "can this order still receive a shipping address / payment proof upload"
 * (addAddress()/uploadPaymentProof() still use it, correctly, since those
 * actions stop making sense once an order is paid), but wrong for "list of
 * orders this buyer has ever placed", which should never depend on payment
 * or fulfillment state.
 *
 * Fixed by dropping the scope from MyCart() entirely — the buyer now sees
 * every order they've placed regardless of payment_status, status, or
 * settled_at.
 */
class OrderVisibleAfterConfirmationTest extends TestCase
{
    use DatabaseTransactions;

    private const API_KEY = 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM';

    private function headers(): array
    {
        return [
            'x-api-key' => self::API_KEY,
            'Accept-Language' => 'en',
        ];
    }

    private function createBuyer(): User
    {
        return User::create([
            'name' => 'Buyer',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'buyer',
            'gender' => 'male',
        ]);
    }

    private function createOrder(User $buyer, array $overrides = []): Order
    {
        $liveVideo = LiveVideo::create(['title' => 'Auction', 'title_ar' => 'مزاد']);

        return Order::create(array_merge([
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'total' => 500,
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ], $overrides));
    }

    private function myCartOrderIds(User $buyer)
    {
        Passport::actingAs($buyer, [], 'api');
        $response = $this->withHeaders($this->headers())->getJson('/api/user/my-cart');
        $response->assertStatus(200);

        return collect($response->json('data'))->pluck('order_id');
    }

    public function test_order_stays_visible_after_being_marked_paid_and_confirmed()
    {
        $buyer = $this->createBuyer();
        $order = $this->createOrder($buyer, ['payment_status' => 'unpaid', 'status' => 'pending']);

        $this->assertTrue($this->myCartOrderIds($buyer)->contains($order->id));

        $order->update(['payment_status' => 'paid', 'status' => 'confirmed']);

        $this->assertTrue($this->myCartOrderIds($buyer)->contains($order->id));
    }

    public function test_order_stays_visible_through_every_later_status()
    {
        $buyer = $this->createBuyer();
        $order = $this->createOrder($buyer, ['payment_status' => 'paid', 'status' => 'preparation']);
        $this->assertTrue($this->myCartOrderIds($buyer)->contains($order->id));

        foreach (['ready_for_delivery', 'shipping', 'delivered'] as $status) {
            $order->update(['status' => $status]);
            $this->assertTrue(
                $this->myCartOrderIds($buyer)->contains($order->id),
                "Order disappeared at status '{$status}'"
            );
        }
    }

    public function test_order_stays_visible_after_being_settled()
    {
        $buyer = $this->createBuyer();
        $order = $this->createOrder($buyer, [
            'payment_status' => 'paid',
            'status' => 'delivered',
            'settled_at' => now(),
        ]);

        $this->assertTrue($this->myCartOrderIds($buyer)->contains($order->id));
    }

    public function test_my_cart_still_only_shows_the_authenticated_buyers_own_orders()
    {
        $buyer = $this->createBuyer();
        $otherBuyer = $this->createBuyer();

        $ownOrder = $this->createOrder($buyer);
        $othersOrder = $this->createOrder($otherBuyer);

        $ids = $this->myCartOrderIds($buyer);

        $this->assertTrue($ids->contains($ownOrder->id));
        $this->assertFalse($ids->contains($othersOrder->id));
    }

    /**
     * Regression guard: add-address/upload-payment-proof must still only
     * apply to unpaid orders — only my-cart's own scoping changed.
     */
    public function test_add_address_still_rejects_an_order_that_is_already_paid()
    {
        $buyer = $this->createBuyer();
        $paidOrder = $this->createOrder($buyer, ['payment_status' => 'paid', 'status' => 'confirmed']);
        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/user/cart/add-address', [
            'order_id' => $paidOrder->id,
            'shipping_address' => 'Some street',
            'city_id' => 1,
            'lat' => '24.7',
            'lng' => '46.6',
        ]);

        $response->assertStatus(200)->assertJson(['success' => false]);
    }
}
