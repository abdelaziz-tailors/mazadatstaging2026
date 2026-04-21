<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SellerSubmissionResource extends JsonResource
{
    public function toArray($request): array
    {
        $images = [];
        $video = null;

        foreach ($this->whenLoaded('media', $this->media ?? []) as $media) {
            if ($media->type === 'image') {
                $images[] = [
                    'id' => $media->id,
                    'file' => Storage::disk('public')->url($media->path),
                    'path' => $media->path,
                    'sort_order' => $media->sort_order,
                ];
            }

            if ($media->type === 'video') {
                $video = [
                    'id' => $media->id,
                    'file' => Storage::disk('public')->url($media->path),
                    'path' => $media->path,
                ];
            }
        }

        return [
            'id' => $this->id,
            'partner_id' => $this->partner_id,
            'partner_name' => optional($this->partner)->name ?? null,
            'sheep_type' => $this->sheep_type,
            'age' => $this->age,
            'expected_price' => $this->expected_price,
            'description' => $this->description,
            'notes' => $this->notes,
            'status' => $this->status,
            'review_note' => $this->review_note,
            'images' => $images,
            'video' => $video,

        ];
    }
}
