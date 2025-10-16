<?php

namespace App\Http\Controllers\api\User\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Providers\DoctorRatingRequest;
use App\Http\Requests\api\User\Providers\MainSearchRequest;
use App\Http\Requests\api\User\Providers\SearchCityRequest;
use App\Http\Requests\api\User\Providers\SearchCityStateRequest;
use App\Http\Requests\api\User\Providers\SearchNameRequest;
use App\Http\Requests\api\User\Providers\SearchRequest;
use App\Http\Resources\User\DoctorRateResource;
use App\Http\Resources\User\ProviderClinicResource;
use App\Http\Resources\User\ProviderNameSearchResource;
use App\Http\Resources\User\ProviderSearchResource;
use App\Http\Resources\User\SearchByNameResource;
use App\Models\Admin;
use App\Models\Branch;
use App\Models\Clinic;
use App\Models\Department;
use App\Models\Rate;
use Illuminate\Http\Request;

use App\Models\Category;
use App\Http\Resources\CategoryResource;

use App\Models\Banner;
use App\Http\Resources\BannerResource;

use App\Models\User\User;
use App\Http\Resources\User\ProviderResource;

use Illuminate\Support\Facades\DB;
use TranslationHelper;
use App\Traits\ResponseTrait;

class DoctorController extends Controller
{
    use ResponseTrait;

    public function list(SearchRequest $request) {


            if ($request->search_type=='city'){

                $clinic = Clinic::where('city_id', $request->city_id )->where('state_id', $request->state_id )->pluck('user_id')->toArray();
                $branch = Branch::where('city_id', $request->city_id )->pluck('id')->toArray();

            }else{
                $clinic = Clinic::select(
                    "clinics.*",

                    DB::raw("6371 *
                         acos(cos(radians(" . $request->lat . ")) *
                         cos(radians(clinics.lat)) *
                         cos(radians(clinics.lng) - radians(" . $request->lng . "))
                         + sin(radians(" . $request->lat . "))
                         * sin(radians(clinics.lat))) AS distance
                        ")
                )->orderBy('distance')->get()
                    ->where('distance', '<=', 5 )
                    ->pluck('user_id')->toArray();

                $branch = Branch::select(
                    "branchs.*",

                    DB::raw("6371 *
                         acos(cos(radians(" . $request->lat . ")) *
                         cos(radians(branchs.lat)) *
                         cos(radians(branchs.lng) - radians(" . $request->lng . "))
                         + sin(radians(" . $request->lat . "))
                         * sin(radians(branchs.lat))) AS distance
                        ")
                )->orderBy('distance')->get()
                    ->where('distance', '<=', 5 )
                    ->pluck('id')->toArray();
            }





        $providers = User::activeProvider()
            ->where(function ($query) use ($clinic,$branch) {
                $query->whereIn('id',$clinic)
                    ->orWhereIn('branch_id',$branch);
            })->where('department_id',$request->department_id);
//
        $providers= $providers->pluck('id')->toArray();
        if ($request->search_type=='city'){

            $pass = Clinic::where('city_id', $request->city_id )->where('state_id', $request->state_id )->pluck('id')->toArray();

        }else {
            $pass = Clinic::select(
                "clinics.*",

                DB::raw("6371 *
                 acos(cos(radians(" . $request->lat . ")) *
                 cos(radians(clinics.lat)) *
                 cos(radians(clinics.lng) - radians(" . $request->lng . "))
                 + sin(radians(" . $request->lat . "))
                 * sin(radians(clinics.lat))) AS distance
                ")
            )->orderBy('distance')->get()
                ->where('distance', '<=', 5 )
                ->pluck('id')->toArray();


        }

        $providers = new ProviderSearchResource($providers,$pass);

        return $this->success_response(NULL, $providers );



    }
    public function listCity(SearchCityRequest $request) {


        $clinic = Clinic::where('city_id', $request->city_id )->pluck('user_id')->toArray();
        $branch = Branch::where('city_id', $request->city_id )->pluck('id')->toArray();

        $providers = User::activeProvider()
            ->where(function ($query) use ($clinic,$branch) {
                $query->whereIn('id',$clinic)
                    ->orWhereIn('branch_id',$branch);
            })->where('department_id',$request->department_id);
        $providers= $providers->pluck('id')->toArray();


        $pass = Clinic::where('city_id', $request->city_id )->pluck('id')->toArray();






        $providers = new ProviderSearchResource($providers,$pass);


        return $this->success_response(NULL, $providers );


    }
    public function listCityState(SearchCityStateRequest $request) {


        $clinic = Clinic::where('city_id', $request->city_id )->where('state_id', $request->state_id )->pluck('user_id')->toArray();
//        $branch = Branch::where('city_id', $request->city_id )->pluck('id')->toArray();

        $providers = User::activeProvider()
            ->where(function ($query) use ($clinic) {
                $query->whereIn('id',$clinic);
//                    ->orWhereIn('branch_id',$branch);
            });
        $providers= $providers->pluck('id')->toArray();


        $pass = Clinic::where('city_id', $request->city_id )->pluck('id')->toArray();






        $providers = new ProviderSearchResource($providers,$pass);


        return $this->success_response(NULL, $providers );


    }
    public function searchByname(SearchNameRequest $request) {


//        dd('name->'.app()->getLocale());
        $providers = User::select('id','name','type')->activeProvider()->where('name','like', '%'.$request->name.'%')->get()->toArray();


        $hospital = Admin::select('id','name','type')->where('type','hospital')->where('name','like', '%'.$request->name.'%')->get()->toArray();
        $department = Department::select('id', 'name->'.app()->getLocale().' as name')
            ->where('name->en','like', '%'.$request->name.'%')->orwhere('name->ar','like', '%'.$request->name.'%')->get()->toArray();



        $all_Search = array_merge($providers,$hospital,$department);

        $collections = collect($all_Search);



        $providers = SearchByNameResource::collection($collections);



        return $this->success_response(NULL, $providers );


    }
    public function search(MainSearchRequest $request) {




        if($request->type == 'doctor'){
            $providers= User::activeProvider()->where('id',$request->search_id)->get();

            $providers =  ProviderNameSearchResource::collection($providers);

        }elseif($request->type == 'hospital'){
            $branch = Branch::select(
                "branchs.*",
                DB::raw("6371 *
                 acos(cos(radians(" . $request->lat . ")) *
                 cos(radians(branchs.lat)) *
                 cos(radians(branchs.lng) - radians(" . $request->lng . "))
                 + sin(radians(" . $request->lat . "))
                 * sin(radians(branchs.lat))) AS distance
                ")
            )->orderBy('distance')->get()
                ->where('admin_id',$request->search_id)
                ->pluck('id')->toArray();
            $hospital = User::activeProvider()->WhereIn('branch_id',$branch)->pluck('id')->toArray();
            $sort_doctor = implode(',',$hospital);
            $providers = User::activeProvider()->whereIn('id',$hospital);
            $providers= $providers->orderByRaw('FIELD(id, '.$sort_doctor.')')->pluck('id')->toArray();
            $pass=[];

            $providers = new ProviderSearchResource($providers,$pass);
        }elseif($request->type == 'department'){








            if ($request->search_type=='city'){

                $clinic = Clinic::where('city_id', $request->city_id )->where('state_id', $request->state_id )->pluck('user_id')->toArray();
                $branch = Branch::where('city_id', $request->city_id )->pluck('id')->toArray();

            }else{
                $clinic = Clinic::select(
                    "clinics.*",

                    DB::raw("6371 *
                 acos(cos(radians(" . $request->lat . ")) *
                 cos(radians(clinics.lat)) *
                 cos(radians(clinics.lng) - radians(" . $request->lng . "))
                 + sin(radians(" . $request->lat . "))
                 * sin(radians(clinics.lat))) AS distance
                ")
                )->orderBy('distance')->get()
                    ->pluck('user_id')->toArray();




                $branch = Branch::select(
                    "branchs.*",

                    DB::raw("6371 *
                 acos(cos(radians(" . $request->lat . ")) *
                 cos(radians(branchs.lat)) *
                 cos(radians(branchs.lng) - radians(" . $request->lng . "))
                 + sin(radians(" . $request->lat . "))
                 * sin(radians(branchs.lat))) AS distance
                ")
                )->orderBy('distance')->get()
                    ->pluck('id')->toArray();

            }



















            $hospital = User::activeProvider()->WhereIn('branch_id',$branch)->pluck('id')->toArray();
            $result = array_merge($clinic, $hospital);

            $sort_doctor = implode(',',$result);





            $providers = User::activeProvider()
                ->where(function ($query) use ($result) {
                    $query->whereIn('id',$result);
                })->where('department_id',$request->search_id);
            $providers= $providers->orderByRaw('FIELD(id, '.$sort_doctor.')')->pluck('id')->toArray();





            if ($request->search_type=='city'){

                $pass = Clinic::where('city_id', $request->city_id )->where('state_id', $request->state_id )->pluck('id')->toArray();

            }else {
                $pass = Clinic::select(
                    "clinics.*",

                    DB::raw("6371 *
                 acos(cos(radians(" . $request->lat . ")) *
                 cos(radians(clinics.lat)) *
                 cos(radians(clinics.lng) - radians(" . $request->lng . "))
                 + sin(radians(" . $request->lat . "))
                 * sin(radians(clinics.lat))) AS distance
                ")
                )->orderBy('distance')->get()
                    ->where('distance', '<=', 5 )
                    ->pluck('id')->toArray();



            }










            $providers = new ProviderSearchResource($providers,$pass);





        }


        return $this->success_response(NULL, $providers );



    }
    public function DoctorRating($id) {




//        if (!auth()->guard('patients')->user()){
//
//            return $this->failed_response(\App\Helpers\TranslationHelper::translate('Un Authenticated'));
//
//        }
//

//        $clinic = Rate::where('user_id',$id)->get();
//        $clinic = Rate::where('user_id',$id)->where('is_active',1)->avg('rate');


        $clinic = Rate::where('user_id',$id)->where('is_active',1)->get();


        return $this->success_response(\App\Helpers\TranslationHelper::translate('Data Fetch successfully'),  DoctorRateResource::collection($clinic));



    }
    public function rating(DoctorRatingRequest $request) {




        if (!auth()->guard('patients')->user()){

            return $this->failed_response(\App\Helpers\TranslationHelper::translate('Un Authenticated'));

        }


        $clinic = Rate::create([
            'user_id' => $request->doctor_id,
            'rate' => $request->rating,
            'comment' => $request->comment,
            'patient_id' => auth()->guard('patients')->user()->id,

        ]);

        return $this->success_response(\App\Helpers\TranslationHelper::translate('Your Rate Added successfully'), new DoctorRateResource($clinic));



    }


}
