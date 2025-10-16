<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;

class LanguageController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $lanuages = Language::select('id', 'name->'.app()->getLocale().' as name', 'code')->where('is_active', 1)->get();
        $data = LanguageResource::collection($lanuages);
        return $this->success_response(NULL, $data);
    }
}
