<?php

namespace App\Http\Controllers\api\Shared;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\GiftResource;
use App\Http\Resources\StateResource;
use App\Http\Resources\User\SoundResource;
use App\Models\Gift;
use App\Models\Sound;
use App\Models\States;
use Illuminate\Http\Request;

use App\Models\City;
use App\Http\Resources\CityResource;

use App\Traits\ResponseTrait;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class SoundController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $state = Sound::select('id', 'name->'.app()->getLocale().' as name', 'artist_name->'.app()->getLocale().' as artist_name',  'sound',  'image')->where('is_active', 1)
          ->get();
        $data =SoundResource::collection($state);
       return $this->success_response(TranslationHelper::translate(' Successfully '), $data);

    }
}
