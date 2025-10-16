<?php

namespace App\Http\Controllers\api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Department;
use App\Http\Resources\DepartmentResource;

use App\Models\Banner;
use App\Http\Resources\BannerResource;

use App\Models\Category;
use App\Http\Resources\CategoryResource;

use App\Http\Resources\User\ProviderResource;
use App\Models\User\User;
use TranslationHelper;
use App\Traits\ResponseTrait;

class DepartmentController extends Controller
{
    use ResponseTrait;

    public function __invoke($id) {
        $department_exists = Department::where('id', $id)->where('is_active', 1)->exists();
        if ($department_exists) {
            $department = Department::select('id', 'name->'.app()->getLocale().' as name', 'image', 'slug', 'is_active')
                ->where('id', $id)->where('is_active', 1)->first();
            $data['department'] = new DepartmentResource($department);

            $top_banners = Banner::select('id', 'image->'.app()->getLocale().' as image')->where('page', 'department')->where('position', 'top')->where('department_id', $id)->get();
            $data['top_banners'] = BannerResource::collection($top_banners);
            $categories = Category::select('id', 'name->'.app()->getLocale().' as name', 'description->'.app()->getLocale().' as description', 'image')
                ->whereNull('category_id')->where('department_id', $id)->where('is_active', 1)->get();
            $data['categories'] = CategoryResource::collection($categories);
            $bottom_banners = Banner::select('id', 'image->'.app()->getLocale().' as image')->where('page', 'department')->where('position', 'bottom')->where('department_id', $id)->get();
            $data['bottom_banners'] = BannerResource::collection($bottom_banners);

            $providers = User::activeProvider()->whereHas('provider_profile', function ($query) use ($id) {
                $query->whereHas('services', function ($service_query) use ($id) {
                    $service_query->where('categories.is_active', 1);
                });
            })->with(['provider_profile'])->get();

            $providers = ProviderResource::collection($providers);
            $data['providers'] = $providers;

            return $this->success_response(NULL, $data);
        }
        else {
            return $this->failed_response(TranslationHelper::translate('Department Not Exists'));
        }
    }
}
