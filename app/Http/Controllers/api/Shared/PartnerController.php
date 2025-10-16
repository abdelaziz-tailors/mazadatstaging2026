<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\PartnerResource;
use App\Models\User\User;
use App\Traits\ResponseTrait;

class PartnerController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $page = User::where('user_type', 'vendor')
            ->where('id','!=', 1)->get();
        $data = PartnerResource::collection($page);
        return $this->success_response('data fatch successfully', $data);
    }

}
