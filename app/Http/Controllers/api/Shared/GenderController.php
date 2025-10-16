<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\DoctorTitleResource;
use App\Http\Resources\GenderResource;
use App\Http\Resources\JobResource;
use App\Models\DoctorTitle;
use App\Models\Gender;
use App\Models\JobTitle;
use Illuminate\Http\Request;

use App\Models\City;
use App\Http\Resources\CityResource;

use App\Traits\ResponseTrait;

class GenderController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $jobs = Gender::select('id', 'name_'.app()->getLocale().' as name')
          ->get();
        $data = GenderResource::collection($jobs);
        return $this->success_response(NULL, $data);
    }
}
