<?php

namespace App\Http\Controllers\api\Shared;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\GiftResource;
use App\Http\Resources\PackageResource;
use App\Http\Resources\StateResource;
use App\Models\Gift;
use App\Models\Package;
use App\Models\States;
use Illuminate\Http\Request;

use App\Models\City;
use App\Http\Resources\CityResource;

use App\Traits\ResponseTrait;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class PackageController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $state = Package::select('id', 'name->'.app()->getLocale().' as name','description->'.app()->getLocale().' as description', 'coin','price', 'image')->where('is_active', 1)
          ->get();
        $data =PackageResource::collection($state);
       return $this->success_response(TranslationHelper::translate(' Successfully '), $data);

    }
}
