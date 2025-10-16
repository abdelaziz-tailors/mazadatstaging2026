<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\DoctorTitleResource;
use App\Http\Resources\JobResource;
use App\Models\DoctorTitle;
use App\Models\JobTitle;
use Illuminate\Http\Request;

use App\Models\City;
use App\Http\Resources\CityResource;

use App\Traits\ResponseTrait;

class DoctorTitleController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $jobs = DoctorTitle::select('id', 'name_'.app()->getLocale().' as name')
          ->get();
        $data = DoctorTitleResource::collection($jobs);
        return $this->success_response(NULL, $data);
    }
}
