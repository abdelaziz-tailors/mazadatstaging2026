<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FirebaseController;
use App\Models\City;
use App\Models\LiveVideo;
use Illuminate\Http\Request;
use App\Traits\ActionTrait;
use Brian2694\Toastr\Facades\Toastr;
use App\Helpers\TranslationHelper;
use App\Jobs\SendFCMNotification;
use Yajra\DataTables\DataTables;
use App\Traits\AuthorizeTrait;
use App\Models\User\User;
use App\Support\PartnerDashboardScope;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    use AuthorizeTrait ,ActionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        return view('dashboard.pages.videos.index',compact('request'));
    }


    // get index data by ajax
    public function get_data (Request $request) {
        $providers = LiveVideo::query()->orderByDesc('date_start_at');
        PartnerDashboardScope::scopeLiveVideos($providers);

        if ($request->boolean('archive')) {
            $providers->where('status', 'end');
        }

        return Datatables::of($providers)

            ->editColumn('title', function(LiveVideo $item) {
                return $item->title;
            })
            ->addColumn('user_name', function(LiveVideo $item) {
                return $item->partner->name ??'';
            })
            ->addColumn('quantity', function(LiveVideo $item) {
                return $item->quantity;
            })
            ->addColumn('start_price', function(LiveVideo $item) {
                return $item->start_price;
            })
            ->addColumn('status', function(LiveVideo $item) {
                return view('dashboard.pages.videos.status')
                    ->with(['item' => $item]);

            })
            ->addColumn('price', function(LiveVideo $item) {
                return  $item->price;
            })
            ->addColumn('buyer', function(LiveVideo $item) {
                return $item->user_auction->name ?? '';
            })
            ->addColumn('auction_time', function(LiveVideo $item) {
                return date('Y-m-d',strtotime($item->date_start_at));

            })

            ->addColumn('action', function(LiveVideo $item) {
                return view('dashboard.pages.videos.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'phone','email', 'status', 'action'])
            ->startsWithSearch()
            -> make(true);
    }
    function show($id){
        $video = LiveVideo::findOrFail($id);
        PartnerDashboardScope::ensureOwnLiveVideo($video);
        return view('dashboard.pages.videos.show',compact('video'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $cities = City::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
        $providers = User::where('user_type', 'vendor');
        if (PartnerDashboardScope::isPartner()) {
            $providers->where('admin_id', Auth::guard('admin')->user()->id);
        }
        $providers = $providers->get();
        $isPartnerDashboard = PartnerDashboardScope::isPartner();

        return view('dashboard.pages.videos.create', compact(['cities', 'providers', 'isPartnerDashboard']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Admin\StoreProviderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $isPartner = PartnerDashboardScope::isPartner();

        $rules = [
            'title'               => 'required|string|max:255',
            'title_ar'            => 'required|string|max:255',
            'date_start_at'       => 'required|date',
            'date_end_at'         => 'required|date|after_or_equal:date_start_at',
            'time_start_at'       => 'required',
            'time_end_at'         => 'required',
            'type'                => 'required|in:live,recorded,photo',
            'city_id'             => 'nullable|exists:cities,id',
            'information'         => 'nullable|string',
            'information_ar'      => 'nullable|string',
            'terms_conditions'    => 'nullable|string',
            'terms_conditions_ar' => 'nullable|string',
            'image'               => 'nullable|array',
            'image.*'             => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ];
        if (! $isPartner) {
            $rules['partners_type'] = 'required|in:single,multiple';
            $rules['partner_id'] = 'nullable|exists:users,id';
            $rules['user_id'] = 'nullable|exists:users,id';
        }

        $request->validate($rules);

        $partnersType = $isPartner ? 'single' : $request->partners_type;
        $partnerId = $isPartner
            ? Auth::guard('admin')->user()->user_id
            : $request->partner_id;
        $userId = $isPartner
            ? Auth::guard('admin')->user()->user_id
            : $request->partner_id;
        $file = [];

        if($request->hasfile('image')) {

            foreach ($request->file('image') as $image) {
                $name = 'image_video/'.rand(11111, 99999) .'_'.$image->getClientOriginalName();
                $image->move(public_path('../storage/app/public/image_video/'), $name);
                $file[] = $name;
            }
        }

        $data=LiveVideo::create([
            'title'=>$request->title,
            'title_ar'=>$request->title_ar,
            'user_id' => $userId,
            'status' => 'pending',
            'image' => json_encode($file),
            'information' => $request->information,
            'information_ar' => $request->information_ar,
            'date_start_at' => $request->date_start_at,
            'date_end_at' => $request->date_end_at,
            'time_start_at' => $request->time_start_at,
            'time_end_at' => $request->time_end_at,
            'terms_conditions' => $request->terms_conditions,
            'terms_conditions_ar' => $request->terms_conditions_ar,
            'city_id' => $request->city_id,
            'admin_id' => Auth::guard('admin')->user()->id,
            'partner_id' => $partnerId,
            'type' => $request->type,
            'partners_type' => $partnersType,
        ]);
        try {
            $firebase = new FirebaseController();
            $firebase->create($data);
        }
        catch(\Exception $t){}
        Toastr::success(TranslationHelper::translate(' Created Successfully'));






        $client_tokens_en = User::whereNotNull('fcm_token')->where('app_lang', 'en')->pluck('fcm_token')->toArray();
        // $coach_tokens_en = Coach::whereNotNull('fcm_token')->where('default_language', 'en')->pluck('fcm_token')->toArray();

        $client_tokens_ar = User::whereNotNull('fcm_token')->where('app_lang', 'ar')->pluck('fcm_token')->toArray();
        // $coach_tokens_ar = Coach::whereNotNull('fcm_token')->where('default_language', 'ar')->pluck('fcm_token')->toArray();

        // $tokens_en = array_merge($client_tokens_en, $coach_tokens_en);
        $tokens_en = $client_tokens_en;

        // $tokens_ar = array_merge($client_tokens_ar, $coach_tokens_ar);
        $tokens_ar = $client_tokens_ar;

        $notification_record = [
            'title_en' => 'New Auction: ' . $data->title,
            'title_ar' => 'مزاد جديد: ' . $data->title_ar,
            'body_en'  => 'Auction "' . $data->title . '" will be held on ' . $data->date_start_at . ' at ' . $data->time_start_at,
            'body_ar'  => 'سيقام المزاد "' . $data->title . '" في ' . $data->date_start_at . ' في ' . $data->time_start_at,
        ];
                // Send using job queue
        dispatch(new SendFCMNotification(
            $tokens_en,
            $notification_record['title_en'],
            $notification_record['body_en'],
        ));
        // Send using job queue
        dispatch(new SendFCMNotification(
            $tokens_ar,
            $notification_record['title_ar'],
            $notification_record['body_ar'],
        ));


        if($request->action == 'add_product'){
//            dd($data->id);
            return redirect()->route('admin.products.create',$data->id);

        }

        return redirect()->route('admin.videos.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $data = LiveVideo::findorfail($id);
        PartnerDashboardScope::ensureOwnLiveVideo($data);

        $cities = City::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
        $providers = User::where('user_type', 'vendor');
        if (PartnerDashboardScope::isPartner()) {
            $providers->where('admin_id', Auth::guard('admin')->user()->id);
        }
        $providers = $providers->get();
        $isPartnerDashboard = PartnerDashboardScope::isPartner();

        return view('dashboard.pages.videos.edit', compact(['data', 'cities', 'providers', 'isPartnerDashboard']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Admin\UpdateProviderRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $live_video = LiveVideo::findorfail($id);

        // if ($live_video->admin_id !== Auth::guard('admin')->user()->id) {
        //     abort(403, 'Unauthorized access.');
        // }

        $isPartner = PartnerDashboardScope::isPartner();

        $rules = [
            'title'               => 'required|string|max:255',
            'title_ar'            => 'required|string|max:255',
            'date_start_at'       => 'required|date',
            'date_end_at'         => 'required|date|after_or_equal:date_start_at',
            'time_start_at'       => 'required',
            'time_end_at'         => 'required',
            'type'                => 'required|in:live,recorded,photo',
            'city_id'             => 'nullable|exists:cities,id',
            'information'         => 'nullable|string',
            'information_ar'      => 'nullable|string',
            'terms_conditions'    => 'nullable|string',
            'terms_conditions_ar' => 'nullable|string',
            'image'               => 'nullable|array',
            'image.*'             => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ];
        if (! $isPartner) {
            $rules['partners_type'] = 'required|in:single,multiple';
            $rules['partner_id'] = 'nullable|exists:users,id';
        }

        $request->validate($rules);
        PartnerDashboardScope::ensureOwnLiveVideo($live_video);

        $partnersType = $isPartner ? 'single' : $request->partners_type;
        $partnerId = $isPartner
            ? Auth::guard('admin')->user()->user_id
            : $request->partner_id;

        if ($live_video->status != 'pending') {
                Toastr::error(TranslationHelper::translate('live_video_cant_be_modified'));
                return redirect()->back();
            }

        $live_video->update([
            'title'=>$request->title,
            'title_ar'=>$request->title_ar,
            'user_id' => Auth::guard('admin')->user()->user_id,
            'status' => 'pending',
            'information' => $request->information,
            'information_ar' => $request->information_ar,
            'date_start_at' => $request->date_start_at,
            'date_end_at' => $request->date_end_at,
            'time_start_at' => $request->time_start_at,
            'time_end_at' => $request->time_end_at,
            'terms_conditions' => $request->terms_conditions,
            'terms_conditions_ar' => $request->terms_conditions_ar,
            'city_id' => $request->city_id,
            'partner_id' => $partnerId,
            'type' => $request->type,
            'partners_type' => $partnersType,
        ]);



        if($request->hasfile('image')) {
            $file=[];

            foreach ($request->file('image') as $image) {
                $name = 'image_video/'.rand(11111, 99999) .'_'.$image->getClientOriginalName();
                $image->move(public_path('../storage/app/public/image_video/'), $name);
                $file[] = $name;
            }
            $live_video->update([
                'image' => json_encode($file),
            ]);
        }




        Toastr::success(TranslationHelper::translate('Data Updated Successfully'));
        return redirect()->route('admin.videos.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
//    public function destroy($id)
//    {
//        $this->authorizable('delete vendor');
//        $admin = User::findorfail($id);
//        $admin->delete();
//        Toastr::success(TranslationHelper::translate('Deleted Successfully'));
//        return redirect()->back();
//    }

}
