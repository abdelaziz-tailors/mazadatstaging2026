<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Resources\JobResource;
use App\Models\Banner;
use App\Models\JobTitle;
use Illuminate\Http\Request;

use App\Models\City;
use App\Http\Resources\CityResource;

use App\Traits\ResponseTrait;

class BannerController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $jobs = Banner::select('id', 'image->'.app()->getLocale().' as image')
         ->whereNull('page')->get();
        $data = BannerResource::collection($jobs);
        return $this->success_response(NULL, $data);
    }
    public function bannerSpecialist() {
        $jobs = Banner::select('id', 'image->'.app()->getLocale().' as image')
         ->where('page','specialist')->get();
        $data = BannerResource::collection($jobs);
        return $this->success_response(NULL, $data);
    }


}
