<?php

namespace App\Http\Controllers\api\User;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\SellerSubmission\StoreSellerSubmissionRequest;
use App\Http\Requests\api\SellerSubmission\UpdateSellerSubmissionRequest;
use App\Http\Resources\User\SellerSubmissionResource;
use App\Models\SellerSubmission;
use App\Models\SellerSubmissionMedia;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerSubmissionController extends Controller
{
    use ResponseTrait;

    public function store(StoreSellerSubmissionRequest $request): JsonResponse
    {

        $submission = SellerSubmission::create([
            'user_id' => auth('api')->id(),
            'partner_id' => $request->partner_id,
            'sheep_type' => $request->sheep_type,
            'age' => $request->age,
            'expected_price' => $request->expected_price,
            'description' => $request->description,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        $this->storeMedia($submission, $request);

        return $this->success_response(
            TranslationHelper::translate(' Added Successfully '),
            new SellerSubmissionResource($submission->load(['media', 'partner:id,name,phone']))
        );
    }

    public function update(UpdateSellerSubmissionRequest $request, $id): JsonResponse
    {
        $submission = DB::transaction(function () use ($request, $id) {
            $submission = SellerSubmission::where('user_id', auth('api')->id())
                ->whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless($submission->status === 'needs edit', 409);

            $history = $submission->review_history ?? [];
            // Preserve legacy modification notes on the first resubmission too.
            if (! $history && $submission->review_note) {
                $history[] = ['status' => 'needs edit', 'note' => $submission->review_note,
                    'occurred_at' => $submission->updated_at->toISOString()];
            }
            $history[] = ['status' => 'pending', 'seller_id' => $submission->user_id,
                'occurred_at' => now()->toISOString()];
            $submission->fill($request->only(['sheep_type', 'age', 'expected_price', 'description', 'notes']));
            $submission->fill(['status' => 'pending', 'review_history' => $history])->save();
            $this->storeMedia($submission, $request);
            return $submission;
        });

        return $this->success_response(
            TranslationHelper::translate('Updated Successfully'),
            new SellerSubmissionResource($submission->load(['media', 'partner:id,name,phone']))
        );
    }

    private function storeMedia(SellerSubmission $submission, Request $request): void
    {
        foreach ($request->file('images', []) as $index => $image) {
            $name = 'seller_submission/' . rand(11111, 99999) . '_' . $image->getClientOriginalName();
            $image->move(public_path('../storage/app/public/seller_submission/'), $name);

            SellerSubmissionMedia::create([
                'seller_submission_id' => $submission->id,
                'type' => 'image',
                'path' => $name,
                'sort_order' => $index,
            ]);
        }

        if ($request->hasFile('video')) {
            $video = $request->file('video');
            $videoName = 'seller_submission/video_' . time() . '_' . uniqid() . '.' . $video->getClientOriginalExtension();
            $video->move(public_path('../storage/app/public/seller_submission/'), $videoName);

            SellerSubmissionMedia::create([
                'seller_submission_id' => $submission->id,
                'type' => 'video',
                'path' => $videoName,
                'sort_order' => 0,
            ]);
        }

    }

    public function myList(): JsonResponse
    {
        $list = SellerSubmission::with(['media', 'partner:id,name'])
            ->where('user_id', auth('api')->id())
            ->latest()
            ->get();

        return $this->success_response(TranslationHelper::translate(' Success'), SellerSubmissionResource::collection($list));
    }

    public function show($id): JsonResponse
    {
        $item = SellerSubmission::with(['media', 'partner:id,name,phone'])
            ->where('user_id', auth('api')->id())
            ->find($id);

        if (!$item) {
            return $this->failed_response(TranslationHelper::translate('not found'), 404);
        }

        return $this->success_response(TranslationHelper::translate(' Success'), new SellerSubmissionResource($item));
    }
}
