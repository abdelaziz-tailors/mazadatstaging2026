<?php

namespace App\Http\Controllers\api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Providers\SearchRequest;
use App\Http\Resources\User\ProviderResource;
use App\Models\User\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    use ResponseTrait;

    public function providersSearch(SearchRequest $request)  {
        $providers = User::activeProvider()->with(['provider_profile']);
        if ($request->has('is_recommended') && $request->is_recommended == 1) {
            $providers = $providers->whereHas('provider_profile', function ($query) {
                $query->where('is_recommended',1);
            });
        }

        if ($request->has('is_recommended') && $request->is_recommended == 1) {
            $providers = $providers->whereHas('provider_profile', function ($query) {
                $query->where('is_recommended',1);
            });
        }

        if ($request->has('nationality_id') ) {
            $providers = $providers->whereHas('provider_profile', function ($query) use($request) {
                $query->where('nationality_id',$request->nationality_id);
            });
        }


        if ($request->has('sub_category_id') ) {
            $providers = $providers->whereHas('provider_profile', function ($query) use($request) {
                $query->whereHas('services', function ($q) use($request) {
                    $q->where('categories.id',$request->sub_category_id);
                });
                
                
                
            });
        }
        

        if ($request->has('gender') ) {
            $providers = $providers->where('gender',$request->gender);
        }
        
        $providers= $providers->get();

        //languages
        // ->whereHas('languages', function ($query) {
        //     $query->where('is_recommended',1);
        // })->get();
        // $providers = ProviderResource::collection($providers);
       

        return $this->success_response(NULL, $providers );


    }
}
