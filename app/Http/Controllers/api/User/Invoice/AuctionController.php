<?php

namespace App\Http\Controllers\api\User\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Providers\SearchRequest;
use App\Http\Resources\User\ProviderInvoiceItemResource;
use App\Http\Resources\User\ProviderInvoiceResource;
use App\Http\Resources\User\ProviderResource;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\User\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    use ResponseTrait;

    public function list(Request $request)  {



        $live=LiveVideo::whereHas('video_items',function($q) use ($request){
            $q->where('user_id',auth('api')->user()->id)->whereHas('user_auction');
            if($request->data_from){
                $q->where('end_at', '>=', $request->data_from);
            }
            if($request->data_to){
                $q->where('end_at', '<=', $request->data_to);
            }
        })->get();
        $data =  ProviderInvoiceResource::collection ($live);
        return $this->success_response(NULL, $data);
    }
    public function Iteam   ($id)  {


        $live=LiveVideoItem::where('user_id',auth('api')->user()->id)->whereHas('user_auction')
        ->get();
        $data =  ProviderInvoiceItemResource::collection ($live);
        return $this->success_response(NULL, $data);
    }
}
