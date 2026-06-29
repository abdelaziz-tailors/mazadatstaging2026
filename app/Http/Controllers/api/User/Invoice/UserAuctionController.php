<?php

namespace App\Http\Controllers\api\User\Invoice;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Profile\UploadAuctionWinVideoRequest;
use App\Http\Requests\api\User\Profile\UploadCartPaymentProofRequest;
use App\Http\Resources\User\AuctionWinVideoResource;
use App\Http\Resources\User\PaymentProofResource;
use App\Http\Resources\User\UserCartAuctionResource;
use App\Http\Resources\User\UserInvoiceItemResource;
use App\Http\Resources\User\UserInvoiceResource;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
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
     * One receipt per order. Optional order_id scopes to one cart order; omit to apply to all active cart orders.
     */
    public function uploadPaymentProof(UploadCartPaymentProofRequest $request): JsonResponse
    {
        $userId = auth('api')->user()->id;
        $orderId = $request->input('order_id') ?? $request->input('id');

        $orders = OrderService::activeCartOrdersQuery($userId, $orderId)
            ->with(['liveVideo', 'items.liveVideoItem'])
            ->get();
        /** @var \Illuminate\Support\Collection<int, \App\Models\Order> $orders */

        if ($orders->isEmpty()) {
            return $this->failed_response(TranslationHelper::translate('No won items for this live auction'));
        }

        $relativePath = $this->savePaymentProofFile($request->file('proof'));

        foreach ($orders as $order) {
            OrderService::applyPaymentProof($order, $relativePath);
        }

        $orders->each->refresh();

        return $this->success_response(
            TranslationHelper::translate('payment_proof_uploaded_successfully'),
            UserCartAuctionResource::collection($orders)
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
     * Settlement invoices for consignor sellers, grouped by order (dashboard style).
     */
    public function sellerInvoiceList()
    {
        $user = auth('api')->user();

        if ($user->user_type !== 'seller') {
            abort(403, TranslationHelper::translate('unauthorized_access'));
        }

        $orders = Order::query()
            ->with([
                'liveVideo',
                'items.liveVideoItem.seller',
                'items.liveVideoItem.pieces',
                'items.services',
            ])
            ->whereHas('items', function ($q) use ($user) {
                $q->where('seller_id', $user->id);
            })
            ->orderByDesc('id')
            ->get();

        $data = $orders
            ->map(fn (Order $order) => $this->formatSellerInvoiceOrder($order, (int) $user->id))
            ->filter()
            ->values();

        return $this->success_response(null, $data);
    }

    /**
     * Partner/vendor invoices grouped by order with per-seller breakdown and all pieces.
     */
    public function partnerInvoiceItemList()
    {
        $user = auth('api')->user();

        if (! in_array($user->user_type, ['vendor', 'buyer_vendor'], true)) {
            abort(403, TranslationHelper::translate('unauthorized_access'));
        }

        $orders = Order::query()
            ->with([
                'liveVideo',
                'items.liveVideoItem.seller',
                'items.liveVideoItem.pieces',
                'items.services',
            ])
            ->where(function ($query) use ($user) {
                $query
                    ->whereHas('items.liveVideoItem', fn ($q) => $q->where('user_id', $user->id))
                    ->orWhereHas('liveVideo', fn ($lv) => $lv->where('partner_id', $user->id));
            })
            ->whereHas('items', fn ($q) => $q->whereNotNull('seller_id'))
            ->orderByDesc('id')
            ->get();

        $data = $orders
            ->map(fn (Order $order) => $this->formatPartnerInvoiceOrder($order))
            ->filter(fn (?array $row) => $row !== null)
            ->values();

        return $this->success_response(null, $data);
    }

    private function formatSellerInvoiceOrder(Order $order, int $sellerId): ?array
    {
        $sellerSummary = OrderService::sellerInvoiceSummariesForOrder($order, $sellerId)->first();

        if (! $sellerSummary) {
            return null;
        }

        $live = $order->liveVideo;

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'invoice_id' => $order->invoiceId(),
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'auction' => [
                'id' => $order->live_video_id,
                'title' => app()->getLocale() === 'ar'
                    ? ($live?->title_ar ?? $live?->title ?? '')
                    : ($live?->title ?? $live?->title_ar ?? ''),
                'title_en' => $live?->title ?? '',
                'title_ar' => $live?->title_ar ?? '',
                'end_at' => $live?->end_at,
            ],
            'seller' => [
                'id' => $sellerSummary['seller_id'],
                'name' => $sellerSummary['seller_name'],
            ],
            'totals' => [
                'gross' => $sellerSummary['gross'],
                'commission' => $sellerSummary['commission'],
                'service_fee' => $sellerSummary['service_fee'],
                'piece_services' => $sellerSummary['piece_services'],
                'net' => $sellerSummary['net'],
            ],
            'items_count' => count($sellerSummary['lines']),
            'items' => $sellerSummary['lines'],
        ];
    }

    private function formatPartnerInvoiceOrder(Order $order): ?array
    {
        $sellerSummaries = OrderService::sellerInvoiceSummariesForOrder($order)->values();

        if ($sellerSummaries->isEmpty()) {
            return null;
        }

        $live = $order->liveVideo;
        $totals = $this->sumSellerSummaries($sellerSummaries);

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'invoice_id' => $order->invoiceId(),
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'auction' => [
                'id' => $order->live_video_id,
                'title' => app()->getLocale() === 'ar'
                    ? ($live?->title_ar ?? $live?->title ?? '')
                    : ($live?->title ?? $live?->title_ar ?? ''),
                'title_en' => $live?->title ?? '',
                'title_ar' => $live?->title_ar ?? '',
                'end_at' => $live?->end_at,
            ],
            'totals' => $totals,
            'sellers_count' => $sellerSummaries->count(),
            'items_count' => (int) $sellerSummaries->sum(fn (array $summary) => count($summary['lines'])),
            'sellers' => $sellerSummaries->map(function (array $summary) {
                return [
                    'seller_id' => $summary['seller_id'],
                    'seller_name' => $summary['seller_name'],
                    'totals' => [
                        'gross' => $summary['gross'],
                        'commission' => $summary['commission'],
                        'service_fee' => $summary['service_fee'],
                        'piece_services' => $summary['piece_services'],
                        'net' => $summary['net'],
                    ],
                    'items_count' => count($summary['lines']),
                    'items' => $summary['lines'],
                ];
            })->values(),
        ];
    }

    private function sumSellerSummaries(Collection $sellerSummaries): array
    {
        return [
            'gross' => round((float) $sellerSummaries->sum('gross'), 2),
            'commission' => round((float) $sellerSummaries->sum('commission'), 2),
            'service_fee' => round((float) $sellerSummaries->sum('service_fee'), 2),
            'piece_services' => round((float) $sellerSummaries->sum('piece_services'), 2),
            'net' => round((float) $sellerSummaries->sum('net'), 2),
        ];
    }
}
