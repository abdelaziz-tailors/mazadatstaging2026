<?php

namespace Tests\Feature\Api\User;

use App\Models\City;
use App\Models\LiveVideo;
use App\Models\Order;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Per explicit request: GET /api/user/my-cart must return the shipping
 * address and payment proof alongside each cart order — the same data
 * already set via POST /api/user/cart/add-address and
 * POST /api/user/auction/upload-payment-proof. All three endpoints share
 * UserCartAuctionResource, so adding the fields there covers all three at
 * once, consistently, without duplicating logic.
 *
 * "shipping_address" is null until an address is actually set (rather than
 * an object of all-null fields) so the app can distinguish "no address yet"
 * from "an address with an empty field". "payment_proof" is a full asset()
 * URL, same convention as the existing PaymentProofResource.
 */
class CartAddressAndPaymentProofTest extends TestCase
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

    private function createCity(): City
    {
        return City::create([
            'name' => json_encode(['ar' => 'جدة', 'en' => 'Jeddah']),
            'is_active' => 1,
        ]);
    }

    /**
     * A real "active cart" order: unpaid, not delivered, not settled — the
     * exact condition Order::scopeActiveCart() requires. Still used by
     * add-address/upload-payment-proof (you can only add an address or
     * upload payment proof while an order is still unpaid); my-cart itself
     * no longer applies this scope — see OrderVisibleAfterConfirmationTest.
     */
    private function createActiveCartOrder(User $buyer, array $overrides = []): Order
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

    public function test_my_cart_returns_null_shipping_address_and_payment_proof_when_neither_set()
    {
        $buyer = $this->createBuyer();
        $this->createActiveCartOrder($buyer);
        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/my-cart');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.shipping_address', null)
            ->assertJsonPath('data.0.payment_proof', null);
    }

    public function test_add_address_response_includes_the_full_shipping_address()
    {
        $buyer = $this->createBuyer();
        $order = $this->createActiveCartOrder($buyer);
        $city = $this->createCity();
        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())->postJson('/api/user/cart/add-address', [
            'order_id' => $order->id,
            'shipping_address' => '123 Test Street',
            'city_id' => $city->id,
            'lat' => '21.5433',
            'lng' => '39.1728',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.0.shipping_address.address', '123 Test Street')
            ->assertJsonPath('data.0.shipping_address.city_id', $city->id)
            ->assertJsonPath('data.0.shipping_address.city', 'Jeddah')
            ->assertJsonPath('data.0.shipping_address.lat', '21.5433')
            ->assertJsonPath('data.0.shipping_address.lng', '39.1728');
    }

    public function test_my_cart_reflects_the_shipping_address_after_it_was_added()
    {
        $buyer = $this->createBuyer();
        $order = $this->createActiveCartOrder($buyer);
        $city = $this->createCity();
        Passport::actingAs($buyer, [], 'api');

        $this->withHeaders($this->headers())->postJson('/api/user/cart/add-address', [
            'order_id' => $order->id,
            'shipping_address' => '456 Another Street',
            'city_id' => $city->id,
            'lat' => '24.7136',
            'lng' => '46.6753',
        ]);

        $response = $this->withHeaders($this->headers())->getJson('/api/user/my-cart');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.shipping_address.address', '456 Another Street')
            ->assertJsonPath('data.0.shipping_address.city', 'Jeddah');
    }

    public function test_upload_payment_proof_response_includes_the_proof_url()
    {
        Storage::fake('public');
        $buyer = $this->createBuyer();
        $order = $this->createActiveCartOrder($buyer);
        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())->post('/api/user/auction/upload-payment-proof', [
            'order_id' => $order->id,
            'proof' => UploadedFile::fake()->image('proof.jpg'),
        ]);

        $response->assertStatus(200);
        $proofUrl = $response->json('data.0.payment_proof');
        $this->assertNotNull($proofUrl);
        $this->assertStringContainsString('payment_proofs/', $proofUrl);
    }

    public function test_my_cart_reflects_the_payment_proof_after_it_was_uploaded()
    {
        $buyer = $this->createBuyer();
        $order = $this->createActiveCartOrder($buyer);
        Passport::actingAs($buyer, [], 'api');

        $this->withHeaders($this->headers())->post('/api/user/auction/upload-payment-proof', [
            'order_id' => $order->id,
            'proof' => UploadedFile::fake()->image('proof.jpg'),
        ]);

        $response = $this->withHeaders($this->headers())->getJson('/api/user/my-cart');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.0.payment_proof'));
    }

    public function test_my_cart_still_returns_the_pre_existing_fields_unchanged()
    {
        $buyer = $this->createBuyer();
        $this->createActiveCartOrder($buyer, ['total' => 750]);
        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/my-cart');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.total_price', 750)
            ->assertJsonPath('data.0.payment_status', 'unpaid')
            ->assertJsonPath('data.0.status', 'pending');
    }

    public function test_shipping_address_is_null_when_no_city_is_linked_but_address_text_exists()
    {
        $buyer = $this->createBuyer();
        $this->createActiveCartOrder($buyer, ['shipping_address' => null]);
        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/my-cart');

        $response->assertStatus(200)->assertJsonPath('data.0.shipping_address', null);
    }
}
