<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;

use App\Models\Country;
use App\Http\Resources\CountryResource;

use App\Traits\ResponseTrait;

class CountryController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $countries = Country::select('id', 'name->'.app()->getLocale().' as name', 'image', 'phone_code')->where('is_active', 1)->get();
        $data = CountryResource::collection($countries);
        return $this->success_response(NULL, $data);
    }
}
