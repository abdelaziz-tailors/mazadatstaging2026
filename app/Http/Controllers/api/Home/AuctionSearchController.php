<?php

namespace App\Http\Controllers\api\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\Home\FilterAuctionsRequest;
use App\Http\Requests\api\Home\SearchAuctionsRequest;
use App\Http\Resources\User\HomeVideoResource;
use App\Models\LiveVideo;
use Illuminate\Http\JsonResponse;
use App\Traits\ResponseTrait;

class AuctionSearchController extends Controller
{
    use ResponseTrait;

    private const EAGER_LOAD = [
        'islike', 'isfavorite', 'all_views', 'partnerData', 'city', 'video_items', 'user_Video',
    ];

    /**
     * Search auctions by title (Arabic or English).
     */
    public function search(SearchAuctionsRequest $request): JsonResponse
    {
        $data = LiveVideo::query()
            ->with(self::EAGER_LOAD)
            ->where(function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request->q.'%')
                    ->orWhere('title_ar', 'like', '%'.$request->q.'%');
            })
            ->orderBy('id', 'desc')
            ->paginate((int) ($request->video_limit ?? 10));

        return $this->paginatedResponse($data);
    }

    /**
     * Filter auctions by status bucket: inprogress, upcoming, or archive.
     */
    public function filter(FilterAuctionsRequest $request): JsonResponse
    {
        $data = LiveVideo::query()
            ->with(self::EAGER_LOAD)
            ->when($request->status === 'inprogress', function ($query) {
                $query->where('status', 'start');
            })
            ->when($request->status === 'archive', function ($query) {
                $query->where('status', 'end');
            })
            ->when($request->status === 'upcoming', function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('status')->orWhereNotIn('status', ['start', 'end']);
                });
            })
            ->orderBy('id', 'desc')
            ->paginate((int) ($request->video_limit ?? 10));

        return $this->paginatedResponse($data);
    }

    private function paginatedResponse($data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Successfully',
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
