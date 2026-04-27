<?php

namespace App\Http\Controllers\api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\PartnerResource;
use App\Models\User\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class SellerUserController extends Controller
{
    use ResponseTrait;

    public function index(): JsonResponse
    {

        $sellers = User::query()
            ->where('user_type', 'seller')
            ->where('is_active', 1)
            ->orderByDesc('id')
            ->paginate(10);

        return response()->json(['success' => true, 'code' => 200, 'message' => 'Successfully',
            'data' => PartnerResource::collection($sellers),
            'pagination' => [
                'total' => $sellers->total(),
                'count' => $sellers->count(),
                'per_page' => $sellers->perPage(),
                'current_page' => $sellers->currentPage(),
                'total_pages' => $sellers->lastPage(),
            ],
        ]);
    }
}
