<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Services\AuctionWalletSettlement;
use App\Services\OrderService;
use App\Support\PartnerDashboardScope;
use App\Traits\ActionTrait;
use App\Traits\AuthorizeTrait;
use Brian2694\Toastr\Facades\Toastr;
use App\Helpers\TranslationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class OrderController extends Controller
{
    use AuthorizeTrait, ActionTrait;

    public function index(Request $request)
    {
        return view('dashboard.pages.orders.index', compact('request'));
    }

    public function get_data(Request $request)
    {
        $orders = Order::query()
            ->with(['buyer', 'liveVideo', 'shippingCity'])
            ->withCount('items');

        PartnerDashboardScope::scopeOrders($orders);

        return Datatables::of($orders)
            ->editColumn('order_number', function (Order $order) {
                return $order->order_number ?? '—';
            })
            ->addColumn('auction_title', function (Order $order) {
                $live = $order->liveVideo;

                return app()->getLocale() === 'ar'
                    ? ($live->title_ar ?? $live->title ?? '—')
                    : ($live->title ?? $live->title_ar ?? '—');
            })
            ->addColumn('status', function (Order $order) {
                return view('dashboard.pages.orders.status')->with(['order' => $order]);
            })
            ->addColumn('city', function (Order $order) {
                return self::formatCityName($order->shippingCity?->name);
            })
            ->addColumn('total', function (Order $order) {
                return number_format($order->total, 2);
            })
            ->addColumn('items_count', function (Order $order) {
                return $order->items_count;
            })
            ->addColumn('buyer', function (Order $order) {
                return $order->buyer->name ?? '—';
            })
            ->addColumn('payment_proof', function (Order $order) {
                return view('dashboard.pages.orders.payment_proof_column')->with(['order' => $order]);
            })
            ->addColumn('action', function (Order $order) {
                return view('dashboard.pages.orders.actions')->with(['order' => $order]);
            })
            ->rawColumns(['status', 'payment_proof', 'action'])
            ->startsWithSearch()
            ->make(true);
    }

    public function show($id)
    {
        return redirect()->route('admin.orders.edit', $id);
    }

    public function edit($id)
    {
        $order = Order::query()
            ->with([
                'buyer',
                'liveVideo',
                'shippingCity',
                'items.liveVideoItem.categoryData',
                'items.liveVideoItem.seller',
                'items.liveVideoItem.user',
                'items.liveVideoItem.pieces',
                'items.services.itemService',
            ])
            ->findOrFail($id);

        PartnerDashboardScope::ensureOwnOrder($order);

        $itemServices = \App\Models\ItemService::query()
            ->where('is_active', true)
            ->when($order->liveVideo?->admin_id, function ($query, $adminId) {
                $query->where('admin_id', $adminId);
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('dashboard.pages.orders.edit', compact('order', 'itemServices'));
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        PartnerDashboardScope::ensureOwnOrder($order);

        $wasPaid = $order->payment_status === 'paid';

        if ($request->payment_status == 'paid' && $request->status == 'pending') {
            $request['status'] = 'confirmed';
        }

        try {
            DB::transaction(function () use ($request, $order, $wasPaid) {
                OrderService::updateOrderStatus(
                    $order,
                    $request->payment_status,
                    $request->status
                );

                if ($request->payment_status === 'paid' && ! $wasPaid) {
                    AuctionWalletSettlement::settleOrderIfNeeded($order->fresh(['items.liveVideoItem.videoLive']));
                }
            });
        } catch (\RuntimeException $e) {
            if (in_array($e->getMessage(), ['insufficient_wallet_balance', 'user_not_found'], true)) {
                $key = $e->getMessage() === 'user_not_found'
                    ? 'buyer_not_found_for_wallet_settlement'
                    : 'buyer_wallet_balance_insufficient_for_payment';
                Toastr::error(TranslationHelper::translate($key));

                return redirect()->route('admin.orders.index');
            }
            throw $e;
        }

        Toastr::success(TranslationHelper::translate('Data Updated Successfully'));

        return redirect()->route('admin.orders.index');
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        PartnerDashboardScope::ensureOwnOrder($order);
        $order->delete();
        Toastr::success(TranslationHelper::translate('Deleted Successfully'));

        return redirect()->back();
    }

    protected static function formatCityName(?string $name): string
    {
        if (! $name) {
            return '';
        }

        $decoded = json_decode($name, true);

        if (is_array($decoded)) {
            return $decoded[app()->getLocale()] ?? $decoded['ar'] ?? $decoded['en'] ?? '';
        }

        return $name;
    }
}
