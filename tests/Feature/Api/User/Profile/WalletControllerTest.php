<?php

namespace Tests\Feature\Api\User\Profile;

use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * GET /api/user/wallet — mobile "my wallet" screen: current balance, total
 * deposits, total withdrawals, and a cursor-paginated transaction history.
 *
 * A transaction is a deposit when its signed `amount` is positive and a
 * withdrawal when negative — wallet_transactions has no separate
 * status/direction column, so the sign of the real stored amount is the
 * single source of truth for both totals, computed with a real SUM()
 * aggregate over the user's entire history (not just the current page).
 */
class WalletControllerTest extends TestCase
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

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'seller',
            'gender' => 'male',
            'wallet_balance' => 0,
        ], $overrides));
    }

    private function createTransaction(User $user, string $type, float $amount, array $overrides = []): WalletTransaction
    {
        return WalletTransaction::create(array_merge([
            'user_id' => $user->id,
            'type' => $type,
            'amount' => $amount,
            'balance_after' => $amount,
        ], $overrides));
    }

    public function test_wallet_endpoint_requires_authentication()
    {
        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_wallet_returns_the_real_wallet_balance_column()
    {
        $user = $this->createUser(['wallet_balance' => 12450.75]);
        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.balance', 12450.75);
    }

    /**
     * available_balance/pending_balance are explicit aliases of
     * balance/dues (same definition as GET /api/user/balance), added so the
     * app never has to derive "available" itself (e.g. via
     * balance - pending, which goes negative whenever pending exceeds the
     * current balance — pending dues aren't a deduction from the wallet,
     * they just haven't been credited to it yet).
     */
    public function test_wallet_returns_available_and_pending_balance_aliases()
    {
        $seller = $this->createUser(['user_type' => 'seller', 'wallet_balance' => 0]);
        $buyer = $this->createUser(['user_type' => 'buyer']);
        $liveVideo = LiveVideo::create([
            'title' => 'Auction',
            'commission_amount' => 0,
            'commission_payer' => 'buyer',
            'service_fee' => 0,
        ]);
        $order = Order::create([
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $buyer->id,
            'total' => 2270,
            'payment_status' => 'paid',
        ]);
        $item = LiveVideoItem::create(['live_video_id' => $liveVideo->id, 'finished_price' => 2270]);
        OrderItem::create([
            'order_id' => $order->id,
            'live_video_item_id' => $item->id,
            'seller_id' => $seller->id,
            'finished_price' => 2270,
            'settled_at' => null,
        ]);

        Passport::actingAs($seller, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200)
            ->assertJsonPath('data.balance', 0)
            ->assertJsonPath('data.available_balance', 0)
            ->assertJsonPath('data.pending_balance', 2270);
    }

    public function test_wallet_pending_balance_is_zero_for_a_buyer_with_no_unpaid_orders()
    {
        $buyer = $this->createUser(['user_type' => 'buyer', 'wallet_balance' => 100]);
        Passport::actingAs($buyer, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200)
            ->assertJsonPath('data.available_balance', 100)
            ->assertJsonPath('data.pending_balance', 0);
    }

    public function test_total_deposits_sums_only_positive_amount_transactions()
    {
        $user = $this->createUser();
        $this->createTransaction($user, 'seller_credit', 5000);
        $this->createTransaction($user, 'partner_credit', 3000);
        $this->createTransaction($user, 'buyer_debit', -1250);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200)->assertJsonPath('data.total_deposits', 8000);
    }

    public function test_total_withdrawals_sums_the_absolute_value_of_negative_amount_transactions()
    {
        $user = $this->createUser();
        $this->createTransaction($user, 'buyer_debit', -1250);
        $this->createTransaction($user, 'adjustment', -300);
        $this->createTransaction($user, 'seller_credit', 5000);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200)->assertJsonPath('data.total_withdrawals', 1550);
    }

    public function test_totals_are_zero_for_a_user_with_no_transactions()
    {
        $user = $this->createUser();
        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_deposits', 0)
            ->assertJsonPath('data.total_withdrawals', 0)
            ->assertJsonCount(0, 'data.transactions');
    }

    public function test_totals_only_include_the_authenticated_users_own_transactions()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();

        $this->createTransaction($user, 'seller_credit', 1000);
        $this->createTransaction($otherUser, 'seller_credit', 99999);
        $this->createTransaction($otherUser, 'buyer_debit', -99999);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_deposits', 1000)
            ->assertJsonPath('data.total_withdrawals', 0);
    }

    public function test_transactions_list_only_shows_the_authenticated_users_own_rows()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();

        $this->createTransaction($user, 'seller_credit', 100, ['description' => 'mine']);
        $this->createTransaction($otherUser, 'seller_credit', 200, ['description' => 'not mine']);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200)->assertJsonCount(1, 'data.transactions');
        $this->assertEquals('mine', $response->json('data.transactions.0.description'));
    }

    public function test_transactions_are_ordered_newest_first()
    {
        $user = $this->createUser();
        $oldest = $this->createTransaction($user, 'seller_credit', 100, ['description' => 'oldest']);
        $newest = $this->createTransaction($user, 'seller_credit', 200, ['description' => 'newest']);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200);
        $ids = collect($response->json('data.transactions'))->pluck('id');
        $this->assertEquals([$newest->id, $oldest->id], $ids->values()->all());
    }

    public function test_transaction_includes_the_linked_orders_order_number_when_present()
    {
        $user = $this->createUser();
        $liveVideo = LiveVideo::create(['title' => 'Auction']);
        $orderNumber = 'ORD-WALLET-TEST-' . random_int(100000, 999999);
        $order = Order::create([
            'order_number' => $orderNumber,
            'live_video_id' => $liveVideo->id,
            'buyer_id' => $user->id,
            'total' => 500,
        ]);
        $this->createTransaction($user, 'seller_credit', 500, ['order_id' => $order->id]);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200);
        $this->assertEquals($orderNumber, $response->json('data.transactions.0.order_number'));
    }

    public function test_transaction_order_number_is_null_when_not_linked_to_an_order()
    {
        $user = $this->createUser();
        $this->createTransaction($user, 'adjustment', 50);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200);
        $this->assertNull($response->json('data.transactions.0.order_number'));
    }

    public function test_cursor_pagination_respects_the_per_page_query_parameter()
    {
        $user = $this->createUser();
        for ($i = 0; $i < 5; $i++) {
            $this->createTransaction($user, 'seller_credit', 10);
        }

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet?per_page=2');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.transactions')
            ->assertJsonPath('data.pagination.has_more_pages', true)
            ->assertJsonPath('data.pagination.per_page', 2);
        $this->assertNotNull($response->json('data.pagination.next_cursor'));
    }

    public function test_cursor_pagination_next_cursor_advances_to_the_next_page_without_overlap()
    {
        $user = $this->createUser();
        $transactions = [];
        for ($i = 0; $i < 5; $i++) {
            $transactions[] = $this->createTransaction($user, 'seller_credit', 10);
        }

        Passport::actingAs($user, [], 'api');

        $firstPage = $this->withHeaders($this->headers())->getJson('/api/user/wallet?per_page=2');
        $cursor = $firstPage->json('data.pagination.next_cursor');
        $this->assertNotNull($cursor);

        $secondPage = $this->withHeaders($this->headers())->getJson('/api/user/wallet?per_page=2&cursor=' . $cursor);

        $firstPageIds = collect($firstPage->json('data.transactions'))->pluck('id')->all();
        $secondPageIds = collect($secondPage->json('data.transactions'))->pluck('id')->all();

        $secondPage->assertStatus(200)->assertJsonCount(2, 'data.transactions');
        $this->assertEmpty(array_intersect($firstPageIds, $secondPageIds));
    }

    public function test_last_page_reports_has_more_pages_false_and_a_null_next_cursor()
    {
        $user = $this->createUser();
        $this->createTransaction($user, 'seller_credit', 10);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet?per_page=5');

        $response->assertStatus(200)
            ->assertJsonPath('data.pagination.has_more_pages', false)
            ->assertJsonPath('data.pagination.next_cursor', null);
    }

    public function test_an_out_of_range_per_page_falls_back_to_the_default_of_15()
    {
        $user = $this->createUser();
        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet?per_page=500');

        $response->assertStatus(200)->assertJsonPath('data.pagination.per_page', 15);
    }

    public function test_transaction_amount_and_balance_after_are_real_stored_values()
    {
        $user = $this->createUser();
        $this->createTransaction($user, 'seller_credit', 4284.50, ['balance_after' => 9450.25]);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200)
            ->assertJsonPath('data.transactions.0.amount', 4284.5)
            ->assertJsonPath('data.transactions.0.balance_after', 9450.25)
            ->assertJsonPath('data.transactions.0.type', 'seller_credit');
    }

    /**
     * The description stored by AuctionWalletSettlement::applyDelta() is
     * always literal English text ("Auction order payment", etc.) — per
     * explicit request, WalletTransactionResource now translates it through
     * TranslationHelper so it comes back in the requester's own language
     * instead of always-English, the same as every other user-facing
     * string in this API.
     */
    public function test_transaction_description_is_translated_to_arabic_when_requested()
    {
        $user = $this->createUser();
        $this->createTransaction($user, 'seller_credit', 500, ['description' => 'Auction seller settlement']);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders([
            'x-api-key' => self::API_KEY,
            'Accept-Language' => 'ar',
        ])->getJson('/api/user/wallet');

        $response->assertStatus(200)
            ->assertJsonPath('data.transactions.0.description', 'تسوية البائع للمزاد');
    }

    public function test_transaction_description_stays_english_when_requested_in_english()
    {
        $user = $this->createUser();
        $this->createTransaction($user, 'buyer_debit', -500, ['description' => 'Auction order payment']);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200)
            ->assertJsonPath('data.transactions.0.description', 'Auction order payment');
    }

    public function test_transaction_with_a_null_description_stays_null_in_both_languages()
    {
        $user = $this->createUser();
        $this->createTransaction($user, 'adjustment', 50, ['description' => null]);

        Passport::actingAs($user, [], 'api');

        $response = $this->withHeaders($this->headers())->getJson('/api/user/wallet');

        $response->assertStatus(200)->assertJsonPath('data.transactions.0.description', null);
    }
}
