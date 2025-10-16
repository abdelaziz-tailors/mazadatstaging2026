<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\ColorResource;
use App\Http\Resources\User\PartnerResource;
use App\Models\Color;
use App\Models\User\User;
use App\Traits\ResponseTrait;

class ColorController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $color = Color::select('id','name->'.app()->getLocale().' as name', 'color')->get();
        $data = ColorResource::collection($color);
        return $this->success_response('data fatch successfully', $data);
    }

}
