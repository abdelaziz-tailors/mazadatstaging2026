<?php

namespace App\Http\Controllers\api\User\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Profile\UpdateProfileBioRequest;
use App\Http\Requests\api\User\Profile\UpdateProfileImageRequest;
use App\Http\Requests\api\User\Profile\UpdateProfileNameRequest;
use App\Helpers\TranslationHelper;

use App\Http\Requests\api\User\Profile\UpdateProfileUserNameRequest;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserNoTokenResource;
use App\Models\Patient;
use App\Models\PatientSearchType;

use App\Http\Resources\UserResource;
use App\Models\ProfileView;
use App\Models\User\User;
use App\Models\UserBlock;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class ProfileActionController extends Controller
{
    use ResponseTrait;

    public function profileView ($id) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }
        ProfileView::updateOrCreate([
            'viewer_id'=>auth('api')->user()->id,
            'user_id'=>$id,

        ]);

        return $this->success_response(TranslationHelper::translate('add_successfully'), '');
    }
    public function profileViewList () {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }
        $viewer=ProfileView::where(
            'user_id',auth('api')->user()->id,
        )->pluck('viewer_id')->toArray();

        $user=User::whereIn('id',$viewer)->get();

        $data =  ProfileResource::collection($user);
        return $this->success_response(NULL, $data);


    }
    public function block ($id) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }
        UserBlock::updateOrCreate([
            'user_id'=>auth('api')->user()->id,
            'blocked_id'=>$id,

        ]);

        return $this->success_response(TranslationHelper::translate('add_successfully'), '');
    }
    public function blockList () {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }
        $myblock=UserBlock::where('blocked_id', auth('api')->user()->id)->pluck('user_id')->toArray();

        $block=UserBlock::where('user_id', auth('api')->user()->id)
            ->pluck('blocked_id')->toArray();

        $allblock=array_merge($myblock,$block);

        $user=User::whereIn('id',$allblock)->get();

        $data =  ProfileResource::collection($user);
        return $this->success_response(NULL, $data);


    }
    public function isBlock ($id) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }
        $myblock=UserBlock::where('user_id',$id)->where('blocked_id', auth('api')->user()->id)->pluck('user_id')->toArray();

        $block=UserBlock::where('blocked_id',$id)->where('user_id', auth('api')->user()->id)
            ->pluck('blocked_id')->toArray();

        $allblock=array_merge($myblock,$block);
        if (empty($allblock)){
            $data=['is_block'=>false];
        }else{
            $data=['is_block'=>true];
        }


        return $this->success_response(NULL, $data);


    }

}
