<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\User\UpdateUserRequest;
use App\Mail\ApproveEmail;
use App\Mail\SupendedEmail;
use App\Models\City;
use App\Models\Contract;
use App\Models\JobTitle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\ActionTrait;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;

use App\Helpers\TranslationHelper;
use App\Models\Country;
use Yajra\DataTables\DataTables;

use App\Traits\AuthorizeTrait;
use App\Models\User\User;

class UserController extends Controller
{
    use AuthorizeTrait ,ActionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $this->authorizable('view users');
        return view('dashboard.pages.users.index',compact('request'));
    }


    // get index data by ajax
    public function get_data (Request $request) {
        // dd($re/)




            $providers = User::where('user_type','buyer_vendor');


        return Datatables::of($providers)

            ->editColumn('name', function(User $item) {
                return $item->name;
            })
            ->addColumn('user_name', function(User $item) {
                return $item->user_name;
            })
            ->addColumn('age', function(User $item) {


                return Carbon::parse( $item['birth_date'])->age;

            })
            ->editColumn('created_at', function(User $item) {
                return date('Y-m-d',strtotime($item->created_at));
            })

            ->editColumn('is_active', function(User $item) {
                return view('dashboard.partials.actions.is_active')
                    ->with(['item' => $item, 'action' => route('admin.users.active_toogler', $item->id)]);
            })
            ->editColumn('image', function(User $item) {

                return view('dashboard.pages.users.image')
                    ->with(['item' => $item]);

            })
            ->editColumn('specialty', function(User $item) {

                return $item->department->name ?? null;

            })
            ->addColumn('action', function(User $item) {
                return view('dashboard.pages.users.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'phone','email', 'status', 'action'])
            ->startsWithSearch()
            -> make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
//    public function create()
//    {
//        $this->authorizable('add provider');
//        $countries = Country::select('id', 'name->'.app()->getLocale().' as name', 'image', 'phone_code')->where('is_active', 1)->get();
//        $department = Department::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
//        $job= JobTitle::select('id', 'name->'.app()->getLocale().' as name')->where('type',0)->where('is_active', 1)->get();
//        $job_bats= JobTitle::select('id', 'name->'.app()->getLocale().' as name')->where('type',2)->where('is_active', 1)->get();
//        $city= City::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
//        $contract_pdf = Contract::where('id', '93')->first();
//        $contract_pdf=Storage::disk('public')->url($contract_pdf->pdf);
//
//        return view('dashboard.pages.providers.create', compact(['countries','department','job','city','contract_pdf','job_bats']));
//    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Admin\StoreProviderRequest  $request
     * @return \Illuminate\Http\Response
     */
//    public function store(StoreProviderRequest $request)
//    {
//
//
//        if($request->hasfile('clinic_photos')) {
//
//            foreach ($request->file('clinic_photos') as $image) {
//                $name = 'clinic_photos/'.rand(11111, 99999) .'_'.$image->getClientOriginalName();
//                $image->move(public_path('../storage/app/public/clinic_photos/'), $name);
//                $data[] = $name;
//            }
//
//        }else{
//
//            $data[]=null;
//        }
//        if (!empty($request->logo)){
//
//            $fileName = 'logo/'.rand(11111, 99999) .'_'.$request->logo->getClientOriginalName();
//            $request->logo->move(public_path('../storage/app/public/logo/'), $fileName);
//        }else{
//            $fileName=null;
//        }
//        if (!empty($request->scientific_certificate_image)){
//
//            $Scientific_certificate_image = 'Scientific_certificate/'.rand(11111, 99999) .'_'.$request->scientific_certificate_image->getClientOriginalName();
//            $request->scientific_certificate_image->move(public_path('../storage/app/public/Scientific_certificate/'), $Scientific_certificate_image);
//        }else{
//            $Scientific_certificate_image=null;
//        }
//        if (!empty($request->syndicate_image)){
//
//            $Syndicate_image = 'Syndicate_image/'.rand(11111, 99999) .'_'.$request->syndicate_image->getClientOriginalName();
//            $request->syndicate_image->move(public_path('../storage/app/public/Syndicate_image/'), $Syndicate_image);
//        }else{
//            $Syndicate_image=null;
//        }
//
//        if (!empty($request->contract)){
//
//            $contract = 'contract/'.rand(11111, 99999) .'_'.$request->contract->getClientOriginalName();
//            $request->contract->move(public_path('../storage/app/public/contract/'), $contract);
//        }else{
//            $contract=null;
//        }
//
//        if (!empty($request->doctor_image)){
//
//            $doctor_image = 'doctor_image/'.rand(11111, 99999) .'_'.$request->doctor_image->getClientOriginalName();
//            $request->doctor_image->move(public_path('../storage/app/public/contract/'), $doctor_image);
//        }else{
//            $doctor_image=null;
//        }
//        if (!empty($request->license)){
//
//            $license = 'license/'.rand(11111, 99999) .'_'.$request->license->getClientOriginalName();
//            $request->license->move(public_path('../storage/app/public/license/'), $license);
//        }else{
//            $license=null;
//        }
//
//
//
//        $doctor_job= json_encode($request->job);
//
//
//
//        $user = User::create([
//            'name' => $request->name,
//            'email' => $request->email,
//            'phone' => $request->phone,
//            'password' => $request->password,
//            'department_id' => $request->department,
//            'doctor_professional_id' => $doctor_job,
//            'city_id' => $request->city,
//            'location' => $request->location,
//            'lat' => $request->lat,
//            'lng' => $request->lng,
//            'type' => 'doctor',
//            'syndicate' => $request->Syndicate,
//            'scientific_certificate' => $request->Scientific_certificate,
//            'syndicate_image' => $Syndicate_image,
//            'scientific_certificate_image' => $Scientific_certificate_image,
//            'clinic_photos' =>json_encode($data),
//            'logo' => $fileName,
//            'contract' => $contract,
//            'seal_name' => $request->seal_name,
//            'contact_by'=>$request->hear_about,
//            'add_id'=>$request->doctor_id,
//            'link'=>$request->link,
//            'doctor_image'=>$doctor_image,
//            'lab_license' => $license,
//            'profile_completed' => 1,
//
//
//        ]);
//        Toastr::success(TranslationHelper::translate(' Created Successfully'));
//        return redirect()->route('admin.providers.index','type='.$user->type);
//    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->authorizable('edit user');
        $user = User::findorfail($id);

        return view('dashboard.pages.users.edit', compact(['user']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Admin\UpdateProviderRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $this->authorizable('edit user');
        $provider = User::findorfail($id);

        $provider->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        Toastr::success(TranslationHelper::translate('Data Updated Successfully'));
        return redirect()->route('admin.users.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function change_password_form($id)
    {
        $this->authorizable('edit user');
        $user = User::find($id);
        return view('dashboard.pages.users.change_password', compact(['user']));
    }


     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function active_toogler ($id, Request $request) {
        $this->authorizable('view users');
        $item = User::findorfail($id);
        $this->trait_active_toogler($item);
    }

    public function save_password(ChangeHospitalPasswordRequest $request, $id)
    {
        $admin = User::findorfail($id);
        $admin->update([
            'password' => bcrypt($request->password)
        ]);
        Toastr::success(TranslationHelper::translate('Administrator Password Changed Successfully'));
        return redirect()->route('admin.providers.index');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorizable('delete user');
        $admin = User::findorfail($id);
        $admin->delete();
        Toastr::success(TranslationHelper::translate('Deleted Successfully'));
        return redirect()->back();
    }

}
