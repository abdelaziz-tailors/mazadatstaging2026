<?php

namespace App\Http\Controllers\api\Shared;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\StateResource;
use App\Models\Category;
use App\Models\States;
use App\Http\Resources\CityResource;

use App\Traits\ResponseTrait;

class CategoryController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $state = Category::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)
          ->get();
        $data =CategoryResource::collection($state);
       return $this->success_response(TranslationHelper::translate(' Successfully '), $data);

    }
}
