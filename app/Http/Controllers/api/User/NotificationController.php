<?php

namespace App\Http\Controllers\api\User;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\NotificationResource;
use App\Models\Notification;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ResponseTrait;

    public function index(Request $request): JsonResponse
    {
        if (! auth('api')->user()) {
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $userId = auth('api')->user()->id;

        $notifications = Notification::forUser($userId)
            ->orderByDesc('id')
            ->paginate((int) ($request->limit ?? 15));

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Successfully',
            'data' => NotificationResource::collection($notifications),
            'unread_count' => Notification::forUser($userId)->unread()->count(),
            'pagination' => [
                'total' => $notifications->total(),
                'count' => $notifications->count(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'total_pages' => $notifications->lastPage(),
            ],
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        if (! auth('api')->user()) {
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $userId = auth('api')->user()->id;

        return $this->success_response(null, [
            'unread_count' => Notification::forUser($userId)->unread()->count(),
        ]);
    }

    public function markAsRead($id): JsonResponse
    {
        if (! auth('api')->user()) {
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $userId = auth('api')->user()->id;

        $notification = Notification::forUser($userId)->whereKey($id)->first();

        if (! $notification) {
            return $this->failed_response(TranslationHelper::translate('notification not found'));
        }

        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        return $this->success_response(null, new NotificationResource($notification));
    }

    public function markAllAsRead(): JsonResponse
    {
        if (! auth('api')->user()) {
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $userId = auth('api')->user()->id;

        Notification::forUser($userId)->unread()->update(['read_at' => now()]);

        return $this->success_response(TranslationHelper::translate('all notifications marked as read'), null);
    }
}
