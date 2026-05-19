<?php

namespace App\Http\Controllers\api\Shared;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\SliderResource;
use App\Models\Slider;
use App\Traits\ResponseTrait;

class SliderController extends Controller
{
    use ResponseTrait;

    public function __invoke()
    {
        $sliders = Slider::select('id', 'image', 'link', 'position')
            ->where('is_active', 1)
            ->orderBy('position')
            ->orderByDesc('id')
            ->get();

        $data = SliderResource::collection($sliders);

        return $this->success_response(TranslationHelper::translate('Successfully'), $data);
    }
}
