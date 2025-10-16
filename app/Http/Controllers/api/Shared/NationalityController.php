<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;

use App\Models\Nationality;
use App\Http\Resources\NationalityResource;

use App\Traits\ResponseTrait;

class NationalityController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $nationalities = Nationality::select('id', 'name->'.app()->getLocale().' as name', 'image')->where('is_active', 1)->get();
        $data = NationalityResource::collection($nationalities);
        return $this->success_response(NULL, $data);
    }
}
