<?php

namespace App\Http\Controllers\api\User\CV;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Profile\CVRequest;
use App\Http\Requests\api\User\Profile\ReUploadCVRequest;
use App\Http\Requests\api\User\Profile\SearchTypeRequest;
use App\Http\Requests\api\User\Profile\UpdateProfileRequest;
use App\Helpers\TranslationHelper;

use App\Http\Resources\User\CVProfileResource;
use App\Models\JobRequest;
use App\Models\Patient;
use App\Models\PatientSearchType;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Auth;

use App\Http\Resources\UserResource;
use App\Models\User\User;
use App\Traits\ResponseTrait;
class ProfileCVController extends Controller
{
    use ResponseTrait;

    public function CreateUpdateCV(CVRequest $request) {
        if (!auth()->guard('patients')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }
        $cv_count = JobRequest::where( 'patient_id',auth()->guard('patients')->user()->id)->count();

        if ($cv_count==0) {
            if (empty($request->cv)) {

                return response()->json([
                    'code' => 200,
                    'success'   => false,
                    'message'   =>'Please Upload Your CV',
                ]);

            }
        }





        $RequestJob = JobRequest::updateOrCreate(
            [
                'patient_id'=>auth()->guard('patients')->user()->id,
            ],[
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'email' => $request->email,
            'phone' => $request->phone,
            'department_id' => $request->department,
            'doctor_professional_id' => $request->job,
            'city_id' => $request->city,
            'another_city' => $request->another_city,
            'years_experience' => $request->years_experience,
        ]);



        if (!empty($request->cv)){

            $cv = 'CV/'.rand(11111, 99999) .'_'.$request->cv->getClientOriginalName();
            $request->cv->move(public_path('../storage/app/public/CV/'), $cv);
            $RequestJob->update(['cv' => $cv]);
        }
        if (!empty($request->image)){

            $image = 'CV/image/'.rand(11111, 99999) .'_'.$request->image->getClientOriginalName();
            $request->image->move(public_path('../storage/app/public/CV/image/'), $image);
            $RequestJob->update(['image' => $image]);
        }
        if (!empty($request->scientific_certificate)){
            $scientific_certificate = 'scientific_certificate/'.rand(11111, 99999) .'_'.$request->scientific_certificate->getClientOriginalName();
            $request->scientific_certificate->move(public_path('../storage/app/public/scientific_certificate/'), $scientific_certificate);
            $RequestJob->update([ 'scientific_certificate'=>$scientific_certificate]);

        }



        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'),  New CVProfileResource($RequestJob));
    }
    public function ReUploadCV(ReUploadCVRequest $request) {
        if (!auth()->guard('patients')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }
        $cv_data = JobRequest::where( 'patient_id',auth()->guard('patients')->user()->id)->first();



        if (!empty($request->cv)){

            $cv = 'CV/'.rand(11111, 99999) .'_'.$request->cv->getClientOriginalName();
            $request->cv->move(public_path('../storage/app/public/CV/'), $cv);
            $cv_data->update(['cv' => $cv]);
        }

        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'),  New CVProfileResource($cv_data));
    }
    public function UserCV() {
        if (!auth()->guard('patients')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));

        }
        $cv_data = JobRequest::where( 'patient_id',auth()->guard('patients')->user()->id)->first();




        return $this->success_response(TranslationHelper::translate('your_account_updated_successfully'),  New CVProfileResource($cv_data));
    }




}
