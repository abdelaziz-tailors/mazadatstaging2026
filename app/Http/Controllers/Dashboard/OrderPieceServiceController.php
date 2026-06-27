<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Order\StoreOrderPieceServiceRequest;
use App\Http\Requests\Dashboard\Order\UpdateOrderPieceServiceRequest;
use App\Helpers\TranslationHelper;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemService;
use App\Services\PieceServiceService;
use App\Support\PartnerDashboardScope;
use Brian2694\Toastr\Facades\Toastr;

class OrderPieceServiceController extends Controller
{
    public function store(StoreOrderPieceServiceRequest $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        PartnerDashboardScope::ensureOwnOrder($order);

        $orderItem = OrderItem::query()
            ->where('order_id', $order->id)
            ->findOrFail($request->input('order_item_id'));

        PieceServiceService::assertAdminCanManageOrderItem($orderItem);

        PieceServiceService::addToOrderItem($orderItem, [
            'item_service_id' => $request->input('item_service_id'),
            'custom_name' => $request->input('custom_name'),
            'price' => $request->input('price'),
        ]);

        Toastr::success(TranslationHelper::translate('item_service_added_successfully'));

        return redirect()->route('admin.orders.edit', $orderId);
    }

    public function update(UpdateOrderPieceServiceRequest $request, $id)
    {
        $service = OrderItemService::query()->findOrFail($id);
        $order = PieceServiceService::assertAdminCanManageOrderItem($service->orderItem);

        $payload = array_filter([
            'price' => $request->input('price'),
            'item_service_id' => $request->input('item_service_id'),
            'custom_name' => $request->input('custom_name'),
        ], fn ($value) => $value !== null);

        PieceServiceService::update($service, $payload);

        Toastr::success(TranslationHelper::translate('item_service_updated_successfully'));

        return redirect()->route('admin.orders.edit', $order->id);
    }

    public function destroy($id)
    {
        $service = OrderItemService::query()->findOrFail($id);
        $order = PieceServiceService::assertAdminCanManageOrderItem($service->orderItem);

        PieceServiceService::delete($service);

        Toastr::success(TranslationHelper::translate('item_service_deleted_successfully'));

        return redirect()->route('admin.orders.edit', $order->id);
    }
}
