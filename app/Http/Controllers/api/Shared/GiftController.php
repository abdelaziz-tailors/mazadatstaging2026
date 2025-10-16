<?php

namespace App\Http\Controllers\api\Shared;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\GiftResource;
use App\Http\Resources\StateResource;
use App\Models\Gift;
use App\Models\States;
use Illuminate\Http\Request;

use App\Models\City;
use App\Http\Resources\CityResource;

use App\Traits\ResponseTrait;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class GiftController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $state = Gift::select('id', 'name->'.app()->getLocale().' as name', 'coin', 'image_svg', 'image_png')->where('is_active', 1)
          ->get();
        $data =GiftResource::collection($state);
       return $this->success_response(TranslationHelper::translate(' Successfully '), $data);

    }
}
