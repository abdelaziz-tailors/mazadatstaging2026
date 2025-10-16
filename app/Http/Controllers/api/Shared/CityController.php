<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\City;
use App\Http\Resources\CityResource;

use App\Traits\ResponseTrait;

class CityController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $cities = City::select('id', 'name->'.app()->getLocale().' as name', 'country_id')->where('is_active', 1)
          ->get();
        $data = CityResource::collection($cities);
        return $this->success_response(NULL, $data);
    }
}
