<?php

namespace App\Http\Controllers\api\User\Invoice;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Profile\UploadAuctionWinVideoRequest;
use App\Http\Requests\api\User\Profile\UploadCartPaymentProofRequest;
use App\Http\Resources\User\AuctionWinVideoResource;
use App\Http\Resources\User\PaymentProofResource;
use App\Http\Resources\User\PartnerInvoiceItemResource;
use App\Http\Resources\User\SellerInvoiceItemResource;
use App\Http\Resources\User\UserInvoiceItemResource;
use App\Http\Resources\User\UserInvoiceResource;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class UserAuctionController extends Controller
{
    use ResponseTrait;

    public function list(Request $request)
    {
        $orders = Order::query()
            ->with(['liveVideo', 'items.liveVideoItem'])
            ->where('buyer_id', auth('api')->user()->id)
            ->when($request->data_from, fn ($q) => $q->whereHas('liveVideo', fn ($lv) => $lv->where('end_at', '>=', $request->data_from)))
            ->when($request->data_to, fn ($q) => $q->whereHas('liveVideo', fn ($lv) => $lv->where('end_at', '<=', $request->data_to)))
            ->orderByDesc('id')
            ->get();

        $data = UserInvoiceResource::collection($orders);

        return $this->success_response(null, $data);
    }

    public function Iteam($id)
    {
        $live = LiveVideoItem::with('order')
            ->where('user_finished_id', auth('api')->user()->id)
            ->get();
        $data = UserInvoiceItemResource::collection($live);

        return $this->success_response(null, $data);
    }

    /**
     * Upload video for a won auction item.
     * Only the winner (user_finished_id) can upload.
     */
    public function uploadWinVideo(UploadAuctionWinVideoRequest $request, $id): JsonResponse
    {
        $item = LiveVideoItem::where('id', $id)
            ->where('user_finished_id', auth('api')->user()->id)
            ->first();

        if (!$item) {
            return $this->failed_response(TranslationHelper::translate('Auction item not found or you are not the winner'));
        }

        if (!$request->hasFile('video')) {
            return $this->failed_response(TranslationHelper::translate('Please choose a video'));
        }

        $file = $request->file('video');

        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['mp4', 'avi', 'wmv', 'mov'])) {
            return $this->failed_response(TranslationHelper::translate('Video must be mp4, avi, wmv or mov'));
        }

        $dir = public_path('auction_win_videos');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $fileName = time() . '_' . uniqid() . '.' . $ext;
        $file->move($dir, $fileName);
        $relativePath = 'auction_win_videos/' . $fileName;
        $item->update(['winner_video' => $relativePath]);

        return $this->success_response(TranslationHelper::translate('Video uploaded successfully'), new AuctionWinVideoResource($item->fresh()));
    }

    /**
     * One receipt per order (live stream cart). Optional live_video_id scopes to one cart.
     */
    public function uploadPaymentProof(UploadCartPaymentProofRequest $request): JsonResponse
    {
        $userId = auth('api')->user()->id;

        $ordersQuery = Order::query()->where('buyer_id', $userId);

        if ($request->filled('live_video_id')) {
            $ordersQuery->where('live_video_id', $request->live_video_id);
        }

        $orders = $ordersQuery->with('items.liveVideoItem')->get();

        if ($orders->isEmpty()) {
            return $this->failed_response(TranslationHelper::translate('No won items for this live auction'));
        }

        $relativePath = $this->savePaymentProofFile($request->file('proof'));

        foreach ($orders as $order) {
            OrderService::applyPaymentProof($order, $relativePath);
        }

        // $updated = LiveVideoItem::with('order')
        //     ->where('user_finished_id', $userId)
        //     ->when($request->filled('live_video_id'), fn ($q) => $q->where('live_video_id', $request->live_video_id))
        //     ->get();

        return $this->success_response(
            TranslationHelper::translate('payment_proof_uploaded_successfully'),
            // PaymentProofResource::collection($updated)
        );
    }

    /**
     * Store proof under public/ like winner videos; path is relative to public for asset().
     */
    private function savePaymentProofFile(UploadedFile $file): string
    {
        $dir = public_path('payment_proofs');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $ext = strtolower($file->getClientOriginalExtension());
        $fileName = time() . '_' . uniqid('', true) . '.' . $ext;
        $file->move($dir, $fileName);

        return 'payment_proofs/' . $fileName;
    }

    /**
     * Settlement invoices for consignor sellers: live streams with sold items where seller_id is the auth user.
     */
    public function sellerInvoiceList()
    {
        $user = auth('api')->user();

        if ($user->user_type !== 'seller') {
            abort(403, TranslationHelper::translate('unauthorized_access'));
        }
        $items = LiveVideoItem::with(['videoLive', 'order'])
            ->where('seller_id', $user->id)
            ->whereNotNull('user_finished_id')
            ->get();

        $data = SellerInvoiceItemResource::collection($items);

        return $this->success_response(null, $data);
    }

    /**
     * Partner/vendor: flat list of sold lots tied to you (item user_id) or to lives you partner on (live partner_id).
     */
    public function partnerInvoiceItemList()
    {
        $user = auth('api')->user();

        if (! in_array($user->user_type, ['vendor', 'buyer_vendor'], true)) {
            abort(403, TranslationHelper::translate('unauthorized_access'));
        }

        $items = LiveVideoItem::with(['videoLive', 'order'])
            ->whereNotNull('user_finished_id')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('videoLive', fn ($lv) => $lv->where('partner_id', $user->id));
            })
            ->orderByDesc('end_at')
            ->get();

        return $this->success_response(null, PartnerInvoiceItemResource::collection($items));
    }
}
