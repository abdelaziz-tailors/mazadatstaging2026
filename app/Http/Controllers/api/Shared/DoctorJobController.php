<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobResource;
use App\Models\JobTitle;
use Illuminate\Http\Request;

use App\Models\City;
use App\Http\Resources\CityResource;

use App\Traits\ResponseTrait;

class DoctorJobController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $jobs = JobTitle::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->where('type', 0)
          ->get();
        $data = JobResource::collection($jobs);
        return $this->success_response(NULL, $data);
    }
}
