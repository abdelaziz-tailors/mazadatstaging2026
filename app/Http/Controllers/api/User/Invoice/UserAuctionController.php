<?php

namespace App\Http\Controllers\api\User\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserInvoiceItemResource;
use App\Http\Resources\User\UserInvoiceResource;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class UserAuctionController extends Controller
{
    use ResponseTrait;

    public function list(Request $request)  {
        $live=LiveVideo::whereHas('video_items',function($q) use ($request){
            $q->where('user_finished_id',auth('api')->user()->id);
            if($request->data_from){
                $q->where('end_at', '>=', $request->data_from);
            }
            if($request->data_to){
                $q->where('end_at', '<=', $request->data_to);
            }

        })->get();
        $data =  UserInvoiceResource::collection ($live);
        return $this->success_response(NULL, $data);
    }
    public function Iteam   ($id)  {


        $live=LiveVideoItem::where('user_finished_id',auth('api')->user()->id)
        ->get();
        $data =  UserInvoiceItemResource::collection ($live);
        return $this->success_response(NULL, $data);
    }
}
