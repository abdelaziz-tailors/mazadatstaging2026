<?php

namespace App\Http\Controllers\api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\PartnerResource;
use App\Models\User\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class PartnerController extends Controller
{
    use ResponseTrait;

    public function index(): JsonResponse
    {
        $partners = User::query()
            ->where('user_type', 'vendor')
            ->where('is_active', 1)
            // Dashboard deletion soft-deletes the linked Admin, not the User.
            // Include trashed admins here so previously deleted partners are hidden too.
            ->whereDoesntHave('admin', function ($query) {
                $query->onlyTrashed()->where('type', 'partner');
            })
            ->orderByDesc('id')
            ->paginate(10);

        return response()->json(['success' => true, 'code' => 200, 'message' => 'Successfully',
            'data' => PartnerResource::collection($partners),
            'pagination' => [
                'total' => $partners->total(),
                'count' => $partners->count(),
                'per_page' => $partners->perPage(),
                'current_page' => $partners->currentPage(),
                'total_pages' => $partners->lastPage(),
            ],
        ]);
    }
}
