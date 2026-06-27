<?php

namespace App\Http\Controllers\api\User\Profile;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Profile\StorePieceServiceRequest;
use App\Http\Requests\api\User\Profile\UpdatePieceServiceRequest;
use App\Http\Resources\User\ItemServiceCatalogResource;
use App\Http\Resources\User\PieceServiceResource;
use App\Models\ItemService;
use App\Models\LiveVideo;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemService;
use App\Services\PieceServiceService;
use App\Traits\ResponseTrait;

class PieceServiceController extends Controller
{
    use ResponseTrait;

    public function types(\Illuminate\Http\Request $request)
    {
        $query = ItemService::query()
            ->where('is_active', true);

        if ($request->filled('order_id')) {
            $order = Order::query()->with('liveVideo')->findOrFail($request->input('order_id'));
            if ($order->liveVideo?->admin_id) {
                $query->where('admin_id', $order->liveVideo->admin_id);
            }
        } elseif ($request->filled('live_video_id')) {
            $live = LiveVideo::query()->findOrFail($request->input('live_video_id'));
            if ($live->admin_id) {
                $query->where('admin_id', $live->admin_id);
            }
        }

        $types = $query->orderBy('sort_order')->orderBy('id')->get();

        return $this->success_response(null, ItemServiceCatalogResource::collection($types));
    }

    public function store(StorePieceServiceRequest $request)
    {
        $orderItem = OrderItem::query()->findOrFail($request->input('order_item_id'));
        PieceServiceService::assertUserOwnsOrderItem($orderItem, (int) auth('api')->id());

        $service = PieceServiceService::addToOrderItem($orderItem, [
            'item_service_id' => $request->input('item_service_id'),
            'custom_name' => $request->input('custom_name'),
            'price' => $request->input('price'),
        ]);

        return $this->success_response(
            TranslationHelper::translate('item_service_added_successfully'),
            new PieceServiceResource($service)
        );
    }

    public function update(UpdatePieceServiceRequest $request, $id)
    {
        $service = OrderItemService::query()->findOrFail($id);
        PieceServiceService::assertUserOwnsOrderItem($service->orderItem, (int) auth('api')->id());

        $payload = array_filter([
            'price' => $request->input('price'),
            'item_service_id' => $request->input('item_service_id'),
            'custom_name' => $request->input('custom_name'),
        ], fn ($value) => $value !== null);

        $service = PieceServiceService::update($service, $payload);

        return $this->success_response(
            TranslationHelper::translate('item_service_updated_successfully'),
            new PieceServiceResource($service)
        );
    }

    public function destroy($id)
    {
        $service = OrderItemService::query()->findOrFail($id);
        PieceServiceService::assertUserOwnsOrderItem($service->orderItem, (int) auth('api')->id());

        PieceServiceService::delete($service);

        return $this->success_response(TranslationHelper::translate('item_service_deleted_successfully'), null);
    }
}
