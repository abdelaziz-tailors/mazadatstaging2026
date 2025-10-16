<?php

namespace App\Http\Controllers;

use App\Http\Resources\User\UserDataResource;
use App\Http\Resources\User\UserMainDataResource;
use App\Http\Resources\User\VideoCommentResource;
use App\Models\Car;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\Provider;
use App\Models\TripRequest;
use App\Transformers\CarsTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FirebaseController extends Controller
{
    private $database;

    public function __construct()
    {
        $this->database = \App\Services\FirebaseService::connect();
    }

    public function create(LiveVideo $video)
    {
        $this->database
            ->getReference('live/'.$video->id)
            ->set([
                'status' =>$video->status,
                'view' =>0

            ]);

        return response()->json('blog has been created');
    }


    public function create_item(LiveVideoItem $item)
    {
        $this->database
            ->getReference('live/'.$item->live_video_id.'/item/'.$item->id)
            ->set([
                'status' =>$item->status
            ]);

        return response()->json('blog has been created');
    }


    public function AddAuctions($item)
    {


        $this->database
            ->getReference('live/'.$item->video_id.'/item/'.$item->live_video_item_id.'/auctions/'.$item->id)
            ->set([
                'id' => $item->id ??'',
                'comment' => $item->comment ?? '-',
                'date' => $item->created_at ?? '-',
                'user'=> New UserMainDataResource($item->user_Video),
            ]);

        return response()->json('blog has been created');
    }


    public function UserAuctions($item)
    {

//        dd($item);

        $this->database
            ->getReference('live/'.$item->video_id.'/item/'.$item->live_video_item_id.'/user_take_item/'.$item->id)
            ->set([
                'id' => $item->id ??'',
                'comment' => $item->comment ?? '-',
                'date' => $item->created_at ?? '-',
                'user'=> New UserMainDataResource($item->user_Video),
            ]);

        return response()->json('blog has been created');
    }





    public function ChangeLiveStatus($live_id, $status)
    {
        $this->database->getReference('live/' . $live_id)
            ->update([
                'status'    => $status
            ]);
    }

    public function LiveCount($live_id, $count)
    {
        $this->database->getReference('live/' . $live_id)
            ->update([
                'view'    => $count
            ]);
    }




    public function ChangeLiveItemStatus($live_id,$item_id, $status)
    {
        $this->database
        ->getReference('live/'.$live_id.'/item/'.$item_id)
            ->update([
                'status'    => $status
            ]);
    }

    public function removeLiveItem($live_id,$item_id)
    {
        $this->database->getReference('live/'.$live_id.'/item/'.$item_id)->remove();
    }
    public function removeLive($video_id)
    {

        $this->database->getReference('live/'.$video_id)->remove();

        return response()->json('live has been remove');
    }


}
