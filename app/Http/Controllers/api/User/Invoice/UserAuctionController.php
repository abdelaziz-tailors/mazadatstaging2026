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
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\ResponseTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;

class UserAuctionController extends Controller
{
    use ResponseTrait;

    public function list(Request $request)
    {
        dd(auth('api')->user()->id);
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
     * Download a buyer's invoice for one of their own orders as a PDF file.
     */
    public function downloadInvoicePdf($id)
    {
        $order = Order::with(['liveVideo', 'items.liveVideoItem'])
            ->where('buyer_id', auth('api')->user()->id)
            ->find($id);

        if (! $order) {
            return $this->failed_response(TranslationHelper::translate('invoice not found'));
        }

        $data = $this->invoicePdfData($order);

        $pdf = Pdf::loadView('pdf.invoice', $data);

        return $pdf->download($data['invoiceNumber'].'.pdf');
    }

    /**
     * Build the data set rendered by resources/views/pdf/invoice.blade.php.
     *
     * "seller_commission" is computed fresh from the auction's own commission
     * rate (not the Order's stored commission_value, which only reflects
     * whichever party — buyer or seller — actually pays it) so both figures
     * can be shown side by side for transparency.
     */
    private function invoicePdfData(Order $order): array
    {
        $live = $order->liveVideo;
        $firstItem = $order->items->first()?->liveVideoItem;
        $commissionPct = (float) ($live?->commission_amount ?? 0);

        return [
            'invoiceNumber' => 'INV-'.$order->id,
            'orderNumber' => $order->order_number,
            'auctionTitle' => $live?->title_ar ?? $live?->title ?? '',
            'itemTitle' => $firstItem?->title_ar ?? $firstItem?->title ?? '',
            'bidValue' => (float) $order->subtotal,
            'buyerCommission' => (float) $order->commission_value,
            'sellerCommission' => round($commissionPct * (float) $order->subtotal / 100, 2),
            'sponsorshipFee' => (float) $order->service_fee_total,
            'vat' => (float) $order->tax_value,
            'total' => (float) $order->total,
            'paymentStatus' => $order->payment_status,
            'issuedAt' => optional($order->created_at)->format('Y-m-d') ?? '',
            'regularFontPath' => 'file://'.str_replace('\\', '/', resource_path('fonts/Cairo-Regular.ttf')),
            'boldFontPath' => 'file://'.str_replace('\\', '/', resource_path('fonts/Cairo-Bold.ttf')),
            'regularLatinFontPath' => 'file://'.str_replace('\\', '/', resource_path('fonts/Cairo-Latin-Regular.ttf')),
            'boldLatinFontPath' => 'file://'.str_replace('\\', '/', resource_path('fonts/Cairo-Latin-Bold.ttf')),
        ];
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
            ->with(['liveVideo', 'shippingCity', 'items.liveVideoItem'])
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
     * Download one seller's own invoice (their line items within a specific
     * order) as a PDF — a directly-clickable link (routes/api/user.php),
     * protected by Laravel's request signing ("signed" middleware) instead
     * of a bearer token, since a plain browser tab/click can't attach an
     * Authorization header. There is no authenticated user in this request
     * at all; authorization comes entirely from the signature itself: the
     * seller_id query param is only ever put there, for that specific order,
     * by formatSellerInvoiceOrder() below — tampering with either the order
     * id in the path or the seller_id in the query string invalidates the
     * signature and the "signed" middleware rejects the request before this
     * method ever runs.
     *
     * The summary is always recomputed scoped to the signed seller_id, never
     * to "whichever seller(s) actually own items in the order" — so even for
     * an order with several sellers in it, each seller's own signed link only
     * ever shows their own lines/totals, never another seller's data mixed in.
     */
    public function downloadSellerInvoicePdf(Request $request, $id)
    {
        $sellerId = (int) $request->query('seller_id');

        $order = Order::with(['liveVideo', 'items.liveVideoItem.seller', 'items.liveVideoItem.pieces', 'items.services'])
            ->whereHas('items', fn ($q) => $q->where('seller_id', $sellerId))
            ->find($id);

        if (! $order) {
            return $this->failed_response(TranslationHelper::translate('invoice not found'));
        }

        $sellerSummary = OrderService::sellerInvoiceSummariesForOrder($order, $sellerId)->first();

        if (! $sellerSummary) {
            return $this->failed_response(TranslationHelper::translate('invoice not found'));
        }

        $data = $this->sellerInvoicePdfData($order, $sellerSummary);

        $pdf = Pdf::loadView('pdf.seller-invoice', $data);

        return $pdf->download($data['invoiceNumber'].'.pdf');
    }

    /**
     * Build the data set rendered by resources/views/pdf/seller-invoice.blade.php.
     */
    private function sellerInvoicePdfData(Order $order, array $sellerSummary): array
    {
        $live = $order->liveVideo;

        return [
            'invoiceNumber' => 'INV-'.$order->id.'-S'.$sellerSummary['seller_id'],
            'orderNumber' => $order->order_number,
            'auctionTitle' => $live?->title_ar ?? $live?->title ?? '',
            'sellerName' => $sellerSummary['seller_name'],
            'lines' => $sellerSummary['lines'],
            'gross' => (float) $sellerSummary['gross'],
            'commission' => (float) $sellerSummary['commission'],
            'serviceFee' => (float) $sellerSummary['service_fee'],
            'pieceServices' => (float) $sellerSummary['piece_services'],
            'net' => (float) $sellerSummary['net'],
            'paymentStatus' => $order->payment_status,
            'issuedAt' => optional($order->created_at)->format('Y-m-d') ?? '',
            'regularFontPath' => 'file://'.str_replace('\\', '/', resource_path('fonts/Cairo-Regular.ttf')),
            'boldFontPath' => 'file://'.str_replace('\\', '/', resource_path('fonts/Cairo-Bold.ttf')),
            'regularLatinFontPath' => 'file://'.str_replace('\\', '/', resource_path('fonts/Cairo-Latin-Regular.ttf')),
            'boldLatinFontPath' => 'file://'.str_replace('\\', '/', resource_path('fonts/Cairo-Latin-Bold.ttf')),
        ];
    }

    /**
     * Partner/vendor invoices — one consolidated invoice per auction
     * (LiveVideo), not per buyer order. A single auction can have several
     * buyers, each with their own Order row; per explicit request these are
     * merged into one invoice so the organizer sees one total per auction
     * instead of a separate invoice per buyer.
     */
    public function partnerInvoiceItemList()
    {
        $user = auth('api')->user();

        if (! in_array($user->user_type, ['vendor', 'buyer_vendor'], true)) {
            abort(403, TranslationHelper::translate('unauthorized_access'));
        }

        $liveVideos = LiveVideo::query()
            ->where(function ($query) use ($user) {
                $query
                    ->where('user_id', $user->id)
                    ->orWhere('partner_id', $user->id);
            })
            ->whereHas('orders.items', fn ($q) => $q->whereNotNull('seller_id'))
            ->orderByDesc('id')
            ->get();

        $data = $liveVideos
            ->map(fn (LiveVideo $liveVideo) => $this->formatPartnerInvoiceAuction($liveVideo, (int) $user->id))
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
            // A directly-clickable, time-limited signed link (no bearer
            // token needed/possible from a plain browser tab) — see
            // downloadSellerInvoicePdf(). The seller_id is baked into the
            // signature itself, scoped to this specific seller and order, so
            // it can never surface another seller's line items even if the
            // same order has several sellers in it, and it stops working
            // once it expires.
            'pdf_url' => URL::temporarySignedRoute(
                'api.seller-invoice.pdf',
                now()->addDay(),
                ['id' => $order->id, 'seller_id' => $sellerSummary['seller_id']]
            ),
        ];
    }

    /**
     * One consolidated invoice for this whole auction, merging every buyer
     * order that has consignor-seller items in it. payment_status/status
     * are deliberately dropped from this top level — those are per-order
     * concepts, and a single auction can span orders in different payment
     * states, so orders_count/paid_orders_count are exposed instead.
     */
    private function formatPartnerInvoiceAuction(LiveVideo $liveVideo, int $organizerId): ?array
    {
        $sellerSummaries = OrderService::sellerInvoiceSummariesForLiveVideo($liveVideo)->values();

        if ($sellerSummaries->isEmpty()) {
            return null;
        }

        $orders = $liveVideo->orders;

        $sellers = $sellerSummaries->map(function (array $summary) use ($organizerId) {
            $isOwnPiece = (int) $summary['seller_id'] === $organizerId;

            $items = collect($summary['lines'])->map(function (array $line) use ($isOwnPiece) {
                $line['partner_earnings'] = $this->partnerEarningsForLine($line, $isOwnPiece);

                return $line;
            })->values();

            return [
                'seller_id' => $summary['seller_id'],
                'seller_name' => $this->sellerDisplayName($summary['seller_name'], $isOwnPiece),
                'totals' => [
                    'gross' => $summary['gross'],
                    'commission' => $summary['commission'],
                    'service_fee' => $summary['service_fee'],
                    'piece_services' => $summary['piece_services'],
                    'partner_earnings' => round((float) $items->sum('partner_earnings'), 2),
                    'net' => $summary['net'],
                ],
                'items_count' => $items->count(),
                'items' => $items->all(),
            ];
        })->values();

        return [
            'auction_id' => $liveVideo->id,
            'invoice_id' => (string) $liveVideo->id,
            'auction' => [
                'id' => $liveVideo->id,
                'title' => app()->getLocale() === 'ar'
                    ? ($liveVideo->title_ar ?? $liveVideo->title ?? '')
                    : ($liveVideo->title ?? $liveVideo->title_ar ?? ''),
                'title_en' => $liveVideo->title ?? '',
                'title_ar' => $liveVideo->title_ar ?? '',
                'end_at' => $liveVideo->end_at,
            ],
            'orders_count' => $orders->count(),
            'paid_orders_count' => $orders->where('payment_status', 'paid')->count(),
            'unpaid_orders_count' => $orders->where('payment_status', '!=', 'paid')->count(),
            'totals' => $this->sumSellerSummaries($sellerSummaries, $sellers),
            'sellers_count' => $sellerSummaries->count(),
            'items_count' => (int) $sellerSummaries->sum(fn (array $summary) => count($summary['lines'])),
            'sellers' => $sellers,
        ];
    }

    /**
     * What the auction owner (organizer/partner) himself earns from a line.
     *
     * Normally: commission + service_fee + piece_services — what the
     * platform credits him for running the auction on a consignor's piece.
     *
     * If the piece itself belongs to the auction owner (he is also the
     * seller_id on that lot), he isn't paying himself a commission — instead
     * he earns the full sale price, PLUS the service_fee and piece_services
     * that would otherwise have been deducted from him as if he were a
     * regular consignor (they're still added to his earnings on top of the
     * price, not subtracted).
     */
    private function partnerEarningsForLine(array $line, bool $isOwnPiece): float
    {
        if ($isOwnPiece) {
            return round((float) $line['price'] + $line['service_fee'] + $line['piece_services'], 2);
        }

        return round($line['commission'] + $line['service_fee'] + $line['piece_services'], 2);
    }

    /**
     * Per explicit request: when a "seller" group is actually the auction
     * owner's own piece, his name is suffixed with "(صاحب المزاد)" so the
     * app can show it's clearly his own piece rather than a consignor's,
     * without needing a separate flag. Scoped to this endpoint only.
     */
    private function sellerDisplayName(string $sellerName, bool $isOwnPiece): string
    {
        if (! $isOwnPiece) {
            return $sellerName;
        }

        return $sellerName . ' (' . TranslationHelper::translate('auction_owner') . ')';
    }

    private function sumSellerSummaries(Collection $sellerSummaries, Collection $sellers): array
    {
        return [
            'gross' => round((float) $sellerSummaries->sum('gross'), 2),
            'commission' => round((float) $sellerSummaries->sum('commission'), 2),
            'service_fee' => round((float) $sellerSummaries->sum('service_fee'), 2),
            'piece_services' => round((float) $sellerSummaries->sum('piece_services'), 2),
            'partner_earnings' => round((float) $sellers->sum(fn (array $s) => $s['totals']['partner_earnings']), 2),
            'net' => round((float) $sellerSummaries->sum('net'), 2),
        ];
    }
}
