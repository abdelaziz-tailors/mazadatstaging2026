<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\CitiesResource;
use App\Http\Resources\StateResource;
use App\Models\States;
use Illuminate\Http\Request;

use App\Models\City;
use App\Http\Resources\CityResource;

use App\Traits\ResponseTrait;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class StateController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $state = City::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)
          ->get();
        $data = CitiesResource::collection($state);
        return $this->success_response(NULL, $data);
    }
}
