<?php

namespace App\Http\Controllers\api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Category;
use App\Http\Resources\CategoryResource;

use App\Models\Banner;
use App\Http\Resources\BannerResource;

use App\Models\User\User;
use App\Http\Resources\User\ProviderResource;

use TranslationHelper;
use App\Traits\ResponseTrait;

class CategoryController extends Controller
{
    use ResponseTrait;

    public function __invoke($id) {
        $category_exists = Category::where('id', $id)->where('is_active', 1)->exists();
        if ($category_exists) {
            $category = Category::select('id', 'name->'.app()->getLocale().' as name',
                'description->'.app()->getLocale().' as description', 'image', 'is_active', 'category_id',
                'department_id','duration')
                ->where('id', $id)->where('is_active', 1)->first();
            $data['category'] = new CategoryResource($category);
            if ($category->category_id == NULL) {
                $banner_page = 'category';

                $providers = User::activeProvider()->whereHas('provider_profile', function ($query) use ($id) {
                    $query->where('category_id', $id);
                })->with(['provider_profile'])->get();
                $providers = ProviderResource::collection($providers);
            }
            else {
                $banner_page = 'sub_category';

                $providers = User::activeProvider()->whereHas('provider_profile', function ($query) use ($id) {
                    $query->whereHas('services', function ($service_query) use ($id) {
                        $service_query->where('service_id', $id);
                    });
                })->with(['provider_profile'])->get();
                $providers = ProviderResource::collection($providers);
            }
            $top_banners = Banner::select('id', 'image->'.app()->getLocale().' as image')->where('page', $banner_page)
            ->where('position', 'top')->where('category_id', $id)->get();
            $data['top_banners'] = BannerResource::collection($top_banners);
            $categories = Category::select('id', 'name->'.app()->getLocale().' as name', 'description->'.app()->getLocale().' as description', 'image','duration')
                ->where('category_id', $id)->where('is_active', 1)->get();
            $data['categories'] = CategoryResource::collection($categories);
            $bottom_banners = Banner::select('id', 'image->'.app()->getLocale().' as image')->where('page', $banner_page)
            ->where('position', 'bottom')->where('category_id', $id)->get();
            $data['bottom_banners'] = BannerResource::collection($bottom_banners);

            $data['providers'] = $providers;
            return $this->success_response(NULL, $data);
        }
        else {
            return $this->failed_response(TranslationHelper::translate('Category Not Exists'));
        }
    }

    public function getRecommendedProviders()  {
        $providers = User::activeProvider()->whereHas('provider_profile', function ($query) {
            $query->where('is_recommended',1);
        })->with(['provider_profile'])->get();
        $providers = ProviderResource::collection($providers);
       

        return $this->success_response(NULL, $providers );


    }
}
