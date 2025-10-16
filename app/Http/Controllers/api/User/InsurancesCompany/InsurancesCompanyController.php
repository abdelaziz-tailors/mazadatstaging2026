<?php

namespace App\Http\Controllers\api\User\InsurancesCompany;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\InsurancesCompany\AddUserInsurancesCompanyRequest;
use App\Http\Requests\api\User\InsurancesCompany\UpdateUserInsurancesCompanyRequest;
use App\Http\Resources\User\PatientInsuranceCompanyResource;
use App\Models\PatientInsuranceCompany;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;



class InsurancesCompanyController extends Controller
{
    use ResponseTrait;

    public function add(AddUserInsurancesCompanyRequest $request) {

        if (!auth()->guard('patients')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        if (!empty($request->image)){

            $image = 'user_insurances/'.rand(11111, 99999) .'_'.$request->image->getClientOriginalName();
            $request->image->move(public_path('../storage/app/public/user_insurances/'), $image);
        }else{
            $image=null;
        }

        $InsuranceCompany=PatientInsuranceCompany::create([
            'name' => $request->name,
            'insurance_company_id' => $request->insurance_company_id,
            'brith_date' => $request->brith_date,
            'id_number' => $request->id_number,
            'expiry_date' => $request->expiry_date,
            'image' => $image,
            'user_id' => auth()->guard('patients')->user()->id,
        ]);
        $data = new PatientInsuranceCompanyResource($InsuranceCompany);
        return $this->success_response(NULL, $data);

    }
    public function list() {

        if (!auth()->guard('patients')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $InsuranceCompany=PatientInsuranceCompany::where('user_id', auth()->guard('patients')->user()->id)->get();
        $data =  PatientInsuranceCompanyResource::collection($InsuranceCompany);
        return $this->success_response(NULL, $data);
    }
    public function show($id) {

        if (!auth()->guard('patients')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $InsuranceCompany=PatientInsuranceCompany::find($id);
        if ($InsuranceCompany->user_id != auth()->guard('patients')->user()->id){

            return $this->failed_response(TranslationHelper::translate('Insurance data not found'));
        }
        $data = new PatientInsuranceCompanyResource($InsuranceCompany);

        return $this->success_response(NULL, $data);
    }


    public function update(UpdateUserInsurancesCompanyRequest $request) {

        if (!auth()->guard('patients')->user()){

            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }

        $InsuranceCompany=PatientInsuranceCompany::find($request->id);
        if ($InsuranceCompany->user_id != auth()->guard('patients')->user()->id){

            return $this->failed_response(TranslationHelper::translate('Insurance data not found'));
        }


        if (!empty($request->image)){

            $image = 'user_insurances/'.rand(11111, 99999) .'_'.$request->image->getClientOriginalName();
            $request->image->move(public_path('../storage/app/public/user_insurances/'), $image);
            $InsuranceCompany->update([
                'image' => $image,
            ]);

        }


        $InsuranceCompany->update([
            'name' => $request->name,
            'insurance_company_id' => $request->insurance_company_id,
            'brith_date' => $request->brith_date,
            'id_number' => $request->id_number,
            'expiry_date' => $request->expiry_date,
            'user_id' => auth()->guard('patients')->user()->id,
        ]);
        $data = new PatientInsuranceCompanyResource($InsuranceCompany);
        return $this->success_response(NULL, $data);

    }
    public function destroy($id) {

        if (!auth()->guard('patients')->user()){
            return $this->failed_response(TranslationHelper::translate('Un Authenticated'));
        }


        $InsuranceCompany = PatientInsuranceCompany::findOrFail($id);

        if ($InsuranceCompany->user_id != auth()->guard('patients')->user()->id){

            return $this->failed_response(TranslationHelper::translate('Insurance data not found'));
        }

        $InsuranceCompany->delete();

        return $this->success_response(TranslationHelper::translate('Deleted Successfully'));
    }





}
