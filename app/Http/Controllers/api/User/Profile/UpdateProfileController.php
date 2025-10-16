<?php

namespace App\Http\Controllers\api\User\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Profile\UpdateProfileBioRequest;
use App\Http\Requests\api\User\Profile\UpdateProfileImageRequest;
use App\Http\Requests\api\User\Profile\UpdateProfileNameRequest;
use App\Helpers\TranslationHelper;
use App\Http\Requests\api\User\Profile\UpdateProfileFileRequest;
use App\Http\Requests\api\User\Profile\UpdateProfileUserNameRequest;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserNoTokenResource;
use App\Models\Patient;
use App\Models\PatientSearchType;

use App\Http\Resources\UserResource;
use App\Models\Admin;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class UpdateProfileController extends Controller
{
    use ResponseTrait;

    public function updateName (UpdateProfileNameRequest $request) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        $user = auth('api')->user();
        $user->update([
            'name' => $request->name,
        ]);


        if (auth('api')->user()->user_type == 'vendor') {
            $admin = Admin::where('user_id', auth('api')->user()->id)->first();
            $admin->update([
                'name' => $request->name,
            ]);
        }
        $data = new UserNoTokenResource(auth('api')->user());
        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'), $data);
    }
    public function updateProfile (UpdateProfileNameRequest $request) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        $user = auth('api')->user();
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);


        if (auth('api')->user()->user_type == 'vendor') {
            $admin = Admin::where('user_id', auth('api')->user()->id)->first();
            $admin->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
            ]);
        }
        $data = new ProfileResource(auth('api')->user());
        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'), $data);
    }





    public function updateUserName (UpdateProfileUserNameRequest $request) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        $user = auth('api')->user();
        $user->update([
            'user_name' => $request->user_name,
        ]);
        $data = new UserNoTokenResource(auth('api')->user());
        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'), $data);
    }
    public function updateBio (UpdateProfileBioRequest $request) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        $user = auth('api')->user();
        $user->update([
            'bio' => $request->bio,
        ]);
        $data = new UserNoTokenResource(auth('api')->user());
        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'), $data);
    }
    public function updateImage (UpdateProfileImageRequest $request) {
        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        $image=$request->file('image') ;
        $image_name = 'user/'.rand(11111, 99999) .'_'.$image->getClientOriginalName();
        $image->move(public_path('../storage/app/public/user/'), $image_name);

        $user = auth('api')->user();
        $user->update([
            'image' => $image_name,
        ]);


        if (auth('api')->user()->user_type == 'vendor') {
            $admin = Admin::where('user_id', auth('api')->user()->id)->first();
            $admin->update([
                'image' => $image_name,
            ]);
        }
        $data = new UserNoTokenResource(auth('api')->user());
        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'), $data);
    }


    public function updateFiles (UpdateProfileFileRequest $request) {

        if (!auth('api')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }

        // if(auth('api')->user()->user_type != 'vendor'){
        //     return $this->failed_response(TranslationHelper::translate('only vendor can update files'));
        // }


        if($request->hasFile('tax_certificate')){
            $tax_certificate=$request->file('tax_certificate') ;
            $tax_certificate_name = 'user/'.rand(11111, 99999) .'_'.$tax_certificate->getClientOriginalName();
            $tax_certificate->move(public_path('../storage/app/public/files/'), $tax_certificate_name);
            $user = auth('api')->user();
            $user->update([
                'tax_certificate' => $tax_certificate_name,
        ]);
        }

        if($request->hasFile('license')){
            $license=$request->file('license') ;
            $license_name = 'user/'.rand(11111, 99999) .'_'.$license->getClientOriginalName();
            $license->move(public_path('../storage/app/public/files/'), $license_name);
            $user = auth('api')->user();
            $user->update([
                'license' => $license_name,
            ]);
        }

        if($request->hasFile('commercial_register')){

            $commercial_register=$request->file('commercial_register') ;
            $commercial_register_name = 'user/'.rand(11111, 99999) .'_'.$commercial_register->getClientOriginalName();
            $commercial_register->move(public_path('../storage/app/public/files/'), $commercial_register_name);
            $user = auth('api')->user();
            $user->update([
                'commercial_register' => $commercial_register_name,
            ]);

        }

        $data = new UserNoTokenResource(auth('api')->user());
        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'), $data);
    }


}
