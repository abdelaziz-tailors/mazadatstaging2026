<?php

namespace App\Http\Controllers\api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Department;
use App\Http\Resources\DepartmentResource;

use App\Models\Banner;
use App\Http\Resources\BannerResource;

use App\Traits\ResponseTrait;

class HomeController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $data = array();
        $banners = Banner::select('id', 'image->'.app()->getLocale().' as image')->where('page', 'home')->get();
        $data['banners'] = BannerResource::collection($banners);

        $departments = Department::select('id', 'name->'.app()->getLocale().' as name', 'image', 'slug')
        ->where('is_active', 1)->get();
        $data['departments'] = DepartmentResource::collection($departments);

        return $this->success_response(NULL, $data);
    }
}
