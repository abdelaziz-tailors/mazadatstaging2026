<?php

namespace App\Http\Controllers\api\User\Profile;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Profile\AddShippingAddress;
use App\Http\Requests\api\User\Profile\UpdateFcmRequest;
use App\Http\Requests\api\User\Profile\UpdateLangRequest;
use App\Http\Resources\User\BalanceResource;
use App\Http\Resources\User\HomeVideoResource;
use App\Http\Resources\User\OrganizerStatsResource;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserCartAuctionResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\VideoItemResource;
use App\Http\Resources\User\WalletTransactionResource;
use App\Models\LiveVideo;
use App\Models\Order;
use App\Models\User\User;
use App\Models\Video;
use App\Models\WalletTransaction;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use App\Traits\ResponseTrait;

class UserProfileController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        $data = new ProfileResource(auth('api')->user());
        return $this->success_response(NULL, $data);
    }
    public function balance() {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        $data = new BalanceResource(auth('api')->user());
        return $this->success_response(NULL, $data);
    }

    /**
     * Simple mobile-app dashboard stats for an organizer (vendor/buyer_vendor)
     * account: total distinct buyers across their own auctions, commissions
     * still pending on unpaid orders, and net profit (commission_value) from
     * orders paid so far this calendar month.
     */
    public function organizerStats() {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        $user = auth('api')->user();
        if (!in_array($user->user_type, ['vendor', 'buyer_vendor'], true)) {
            return response()->json(['success' => false, 'code' => 403, 'message' => TranslationHelper::translate('Un-Authorized Access')], 403);
        }

        $data = new OrganizerStatsResource($user);
        return $this->success_response(NULL, $data);
    }

    /**
     * "My wallet" screen: current balance (the real, live User.wallet_balance
     * column — the same source of truth used by BalanceResource), real
     * SUM() aggregates for total deposits/withdrawals across the user's
     * entire wallet_transactions history (not just the current page), and
     * a cursor-paginated transaction list for performance on long histories.
     *
     * A transaction is a "deposit" when its signed amount is positive
     * (money added to the wallet) and a "withdrawal" when negative (money
     * deducted) — wallet_transactions has no separate status/direction
     * column, so the signed amount itself is the single source of truth,
     * avoiding any ambiguity from the free-form "type" field.
     */
    public function wallet(Request $request) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $user = auth('api')->user();

        $sums = WalletTransaction::query()
            ->where('user_id', $user->id)
            ->selectRaw('COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) as total_deposits')
            ->selectRaw('COALESCE(SUM(CASE WHEN amount < 0 THEN -amount ELSE 0 END), 0) as total_withdrawals')
            ->first();

        $perPage = (int) $request->query('per_page', 15);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 15;

        $transactions = WalletTransaction::query()
            ->with('order:id,order_number')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->cursorPaginate($perPage);

        // Same "pending" definition as BalanceResource: unpaid orders owed by
        // a buyer, plus not-yet-settled seller/vendor net — never a deduction
        // from wallet_balance itself, since it hasn't been credited yet.
        // available_balance is always the current wallet_balance, exposed as
        // its own explicit field so the app never has to derive it (e.g. via
        // balance - pending_balance, which goes negative whenever pending
        // exceeds the current balance).
        $dues = 0.0;
        if (in_array($user->user_type, ['buyer', 'buyer_vendor'], true)) {
            $dues += OrderService::unpaidBuyerTotal($user->id);
        }
        if (in_array($user->user_type, ['seller', 'vendor', 'buyer_vendor'], true)) {
            $dues += OrderService::unsettledSellerNet($user->id);
        }

        return $this->success_response(NULL, [
            'balance' => (float) ($user->wallet_balance ?? 0),
            'available_balance' => (float) ($user->wallet_balance ?? 0),
            'pending_balance' => round($dues, 2),
            'total_deposits' => round((float) $sums->total_deposits, 2),
            'total_withdrawals' => round((float) $sums->total_withdrawals, 2),
            'transactions' => WalletTransactionResource::collection($transactions->items()),
            'pagination' => [
                'per_page' => $perPage,
                'next_cursor' => optional($transactions->nextCursor())->encode(),
                'prev_cursor' => optional($transactions->previousCursor())->encode(),
                'has_more_pages' => $transactions->hasMorePages(),
            ],
        ]);
    }

    /**
     * The buyer's full order list — every order they've ever placed, not
     * just still-unpaid ones. This used to reuse Order::scopeActiveCart()
     * (unpaid, not delivered, not settled), which is the right scope for
     * "which orders can still receive an address/payment proof"
     * (addAddress()/uploadPaymentProof() below), but wrong here: once the
     * dashboard marks an order paid + confirmed, it stopped being "active"
     * and silently vanished from the buyer's own order list in the app.
     * Orders should stay visible to their buyer regardless of payment
     * status, order status, or settlement.
     */
    public function MyCart() {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $data = Order::query()
            ->with(['liveVideo', 'shippingCity', 'items.services.itemService', 'items.liveVideoItem.categoryData', 'items.liveVideoItem.videoLive.user_Video', 'items.liveVideoItem.user_auction', 'items.liveVideoItem.pieces'])
            ->where('buyer_id', auth('api')->user()->id)
            ->orderByDesc('id')
            ->get();

        return $this->success_response(null, UserCartAuctionResource::collection($data));
    }

    public function addAddress(AddShippingAddress $request) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $userId = auth('api')->user()->id;
        $orderId = $request->input('order_id') ?? $request->input('id');

        $orders = OrderService::activeCartOrdersQuery($userId, $orderId)
            ->with(['liveVideo', 'items.liveVideoItem.categoryData', 'items.liveVideoItem.videoLive.user_Video', 'items.liveVideoItem.user_auction', 'items.liveVideoItem.pieces'])
            ->get();

        if ($orders->isEmpty()) {
            return $this->failed_response(TranslationHelper::translate('No won items for this live auction'));
        }

        foreach ($orders as $order) {
            OrderService::applyShippingAddress($order, [
                'address' => $request->shipping_address,
                'city_id' => $request->city_id,
                'lat' => $request->lat,
                'lng' => $request->lng,
            ]);
        }

        $orders->each->refresh();
        // Reloaded explicitly (not just via ->with() beforehand) since the
        // city_id used to resolve this relation only just changed above.
        $orders->load('shippingCity');

        return $this->success_response(
            TranslationHelper::translate('shipping_address_added_successfully'),
            UserCartAuctionResource::collection($orders)
        );
    }

    public function otherUserprofile($user_name) {
        $user=User::where('user_name',$user_name)->first();

        if (!$user){
            return $this->failed_response(TranslationHelper::translate('User Not Found'));


        }
        $data = new ProfileResource($user);
        return $this->success_response(NULL, $data);
    }




    public function logout(){
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        auth('api')->user()->token()->revoke();
        return $this->success_response(TranslationHelper::translate('Successfully logged out'),'');

    }

    public function deleteAccount(){
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }
       auth('api')->user()->delete();

        auth('api')->user()->token()->revoke();
        return $this->success_response(TranslationHelper::translate('Account Deleted Successfully'),'');

    }




    public function updateFcm(UpdateFcmRequest $request) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }


        $patients = auth('api')->user();
        $patients->update([
            'fcm_token' => $request->fcm_token,
        ]);
        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'), '');
    }
    public function updateLang(UpdateLangRequest $request) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }


        $patients = auth('api')->user();
        $patients->update([
            'app_lang' => $request->lang,
        ]);
        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'), '');
    }


    public function myVideo(Request $request)
    {
        if (!auth('api')->user()) {


            return response()->json(['success' => false, 'code' => 403, 'message' => TranslationHelper::translate('Un-Authorized Access')], 403);
        }

        $data=Video::where(function ($query) {
            $query->where('view_permissions','!=',3)
                ->Where('user_id',auth('api')->user()->id);
        })->orderBy('id', 'desc')->paginate((int)$request->video_limit ?? 10);
        return response()->json(['success' => true, 'code' => 200, 'message' => 'Successfully',
            'data' => HomeVideoResource::collection($data),
            'pagination' => [
                'total' => $data->total(),
                'count' => $data->count(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'total_pages' => $data->lastPage(),
                'links' => [
                    'prev' => $data->previousPageUrl(),
                    'next' => $data->nextPageUrl(),
                ],
            ],
        ]);


    }
    public function OtherVideo(Request $request)
    {
        if (!auth('api')->user()) {


            return response()->json(['success' => false, 'code' => 403, 'message' => TranslationHelper::translate('Un-Authorized Access')], 403);
        }


        $data=Video::where(function ($query) {
            $query->where('view_permissions','!=',3)
                ->orWhereNull('view_permissions');

        })->Where('user_id',auth('api')->user()->id)->orderBy('id', 'desc')->paginate((int)$request->video_limit ?? 10);
        return response()->json(['success' => true, 'code' => 200, 'message' => 'Successfully',
            'data' => HomeVideoResource::collection($data),
            'pagination' => [
                'total' => $data->total(),
                'count' => $data->count(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'total_pages' => $data->lastPage(),
                'links' => [
                    'prev' => $data->previousPageUrl(),
                    'next' => $data->nextPageUrl(),
                ],
            ],
        ]);


    }


}
