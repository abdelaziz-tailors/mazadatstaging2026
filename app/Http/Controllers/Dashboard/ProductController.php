<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FirebaseController;
use App\Http\Requests\Dashboard\User\UpdateUserRequest;
use App\Mail\ApproveEmail;
use App\Mail\SupendedEmail;
use App\Models\Age;
use App\Models\AnimalPen;
use App\Models\Category;
use App\Models\City;
use App\Models\Color;
use App\Models\Contract;
use App\Models\Department;
use App\Models\JobTitle;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\ActionTrait;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use App\Helpers\TranslationHelper;
use App\Http\Requests\Dashboard\Admin\ChangeHospitalPasswordRequest;
use App\Http\Requests\Dashboard\Providers\StoreProviderRequest;
use App\Http\Requests\Dashboard\Providers\UpdateProviderRequest;
use App\Models\Country;
use App\Models\LiveVideoComment;
use Yajra\DataTables\DataTables;

use App\Traits\AuthorizeTrait;
use App\Models\User\User;
use App\Support\PartnerDashboardScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    use AuthorizeTrait ,ActionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index($id, Request $request)
    {
        $live = LiveVideo::findOrFail($id);
        PartnerDashboardScope::ensureOwnLiveVideo($live);
        $request->merge(['id' => $id]);

        return view('dashboard.pages.products.index', compact('request'));
    }


    // get index data by ajax
    public function get_data ($id,Request $request) {
        $live = LiveVideo::findOrFail($id);
        PartnerDashboardScope::ensureOwnLiveVideo($live);

        $providers = LiveVideoItem::where('live_video_id',$id);


        return Datatables::of($providers)

            ->editColumn('title', function(LiveVideoItem $item) {
                return $item->title;
            })
            ->editColumn('title_ar', function(LiveVideoItem $item) {
                return $item->title_ar;
            })
            ->addColumn('category', function(LiveVideoItem $item) {
                return $item->categoryData->name ??'';
            })
            ->addColumn('ageData', function(LiveVideoItem $item) {
                return $item->age ??'';
            })
            ->addColumn('start_price', function(LiveVideoItem $item) {
                return $item->start_price;
            })
            ->addColumn('status', function(LiveVideoItem $item) {
//                return  TranslationHelper::translate($item->status);
                return view('dashboard.pages.products.status')
                    ->with(['item' => $item]);

            })
            ->addColumn('shipping_address', function(LiveVideoItem $item) {
                return $item->addressData->address ?? $item->address ?? '';
            })
            ->addColumn('finished_price', function(LiveVideoItem $item) {
                return  $item->finished_price;
            })
            ->addColumn('buyer', function(LiveVideoItem $item) {
                return $item->user_auction->name ?? '';
            })

            ->addColumn('action', function(LiveVideoItem $item) {
                return view('dashboard.pages.products.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'phone','email', 'status', 'action'])
            ->startsWithSearch()
            -> make(true);
    }
    function show($id){
        $video=LiveVideo::find($id);
        return view('dashboard.pages.videos.show',compact('video'));

    }


    public function comments_get_data($id,Request $request){

        $comments = LiveVideoComment::where('live_video_item_id',$id);

        return Datatables::of($comments)
            ->editColumn('comment', function(LiveVideoComment $item) {
                return $item->comment;
            })
            ->addColumn('user', function(LiveVideoComment $item) {
                return $item->user_Video->name ?? '';
            })
            ->addColumn('action', function(LiveVideoComment $item) {
                return view('dashboard.pages.products.actions-comment')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'phone','email', 'status', 'action'])
            ->startsWithSearch()
            -> make(true);
    }


    public function comments($id){

        return view('dashboard.pages.products.index-comment',compact('id'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create($id)
    {

        //$this->authorizable('add video');
        $live_video = LiveVideo::findOrFail($id);
        PartnerDashboardScope::ensureOwnLiveVideo($live_video);

        $ages = Age::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
        $colors = Color::select('id','color', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
        $categories = Category::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1);
        PartnerDashboardScope::scopeCategories($categories);
        $categories = $categories->get();
        $animal_pens = AnimalPen::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
        $providers = User::where('user_type', 'vendor');
        if (PartnerDashboardScope::isPartner()) {
            $providers->where('admin_id', Auth::guard('admin')->user()->id);
        }
        $providers = $providers->get();

        return view('dashboard.pages.products.create', compact([    'providers','categories','colors','ages','id','animal_pens','live_video']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Admin\StoreProviderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $live_video = LiveVideo::find($request->video_id);
        if ($live_video) {
            PartnerDashboardScope::ensureOwnLiveVideo($live_video);
        }

        $rules = [
            'user_id'                => 'nullable|exists:users,id',
            'title'                  => 'required|string|max:255',
            'title_ar'               => 'required|string|max:255',
            'age'                    => 'required',
            'start_price'            => 'required|numeric|min:0',
            'bidding'                => 'required|numeric|min:0',
            'quantity'               => 'nullable|integer|min:0',
            'piece_multiplier_number'=> 'nullable|string|max:100',
            'identifier'             => 'nullable|string|max:100',
            'baham_count'            => 'nullable|string|max:100',
            'address'                => 'nullable|string',
            'information'            => 'nullable|string',
            'information_ar'         => 'nullable|string',
            'terms'                  => 'nullable|string',
            'terms_ar'               => 'nullable|string',
            'health_certificate'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'image'                  => 'nullable|array',
            'image.*'                => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'video'                  => 'nullable|file|mimes:mp4,avi,wmv,flv|max:204800',
        ];

        if ($live_video && $live_video->type === 'recorded') {
            $rules['video'] = 'required|file|mimes:mp4,avi,wmv,flv|max:204800';
        }

        $request->validate($rules);






        $file = [];

        if($request->hasfile('image')) {

            foreach ($request->file('image') as $image) {
                $name = 'image_video/'.rand(11111, 99999) .'_'.$image->getClientOriginalName();
                $image->move(public_path('../storage/app/public/image_video/'), $name);
                $file[] = $name;
            }
        }

        if($request->hasfile('health_certificate')) {
            $extension = $request->file('health_certificate')->getClientOriginalExtension();
            $newFileName = 'health_cert_' . time() . '_' . uniqid() . '.' . $extension;
            $request->file('health_certificate')->move(public_path('../storage/app/public/health_certificate/'), $newFileName);
            $health_certificate = 'health_certificate/' . $newFileName;
        }


        if($request->hasfile('video')) {
            $extension = $request->file('video')->getClientOriginalExtension();
            $newFileName = 'video_' . time() . '_' . uniqid() . '.' . $extension;
            $request->file('video')->move(public_path('../storage/app/public/video/'), $newFileName);
            $video = 'video/' . $newFileName;


        }


        $add_iteam=LiveVideoItem::create([
            'live_video_id' => $request->video_id,
            'title' => $request->title,
            'title_ar' => $request->title_ar,
            'status'=>'pending',
            'user_id' => $request->user_id ?? null,
            'image' => json_encode($file),
            'category_id' => $request->category_id ?? null,
            'information' => $request->information ?? null,
            'information_ar' => $request->information_ar ?? null,
            'weight' => $request->weight ?? null,
            'age_id' => $request->age_id ?? null,
            'color_id'=>$request->color_id,
            'type'=>$request->type,
            'date_barth'=>$request->date_barth,
            'animal_pen_id'=>$request->animal_pen_id,
            'start_price' => $request->start_price ?? 0,
            'health_certificate' => $health_certificate ?? null,
            'video' => $video ?? null,
            'address'=>$request->address,
            'age'=>$request->age,
            'age_type'=>$request->age_type,
            'terms'=>$request->terms,
            'terms_ar'=>$request->terms_ar,
            'bidding'=>$request->bidding,
            'quantity'=>$request->quantity ?? 0,
            'piece_multiplier_number' => $request->piece_multiplier_number ?? null,
            'identifier' => $request->identifier ?? null,
            'baham_count' => $request->baham_count ?? null,
            'show_partner_name' => $request->has('show_partner_name') ? true : false,
        ]);


        try {
            $firebase = new FirebaseController();
            $firebase->create_item($add_iteam);
        }
        catch(\Exception $t){}



        try {
            $firebase = new FirebaseController();
            $firebase->create_item($add_iteam);
        }
        catch(\Exception $t){}

        Toastr::success(TranslationHelper::translate(' Created Successfully'));
        if($request->action == 'add_product'){
            return redirect()->route('admin.products.create',$add_iteam->live_video_id);

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
        //$this->authorizable('edit video');
        $data = LiveVideoItem::findorfail($id);
        $ages = Age::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
        $colors = Color::select('id','color', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
        $categories = Category::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
        $animal_pens = AnimalPen::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
        $providers = User::where('user_type','vendor')->get();
        $live_video = LiveVideo::find($data->live_video_id);

        return view('dashboard.pages.products.edit', compact(['providers','data','categories','colors','ages','animal_pens','live_video']));

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
        //$this->authorizable('edit video');
        $live_video = LiveVideoItem::findorfail($id);

        // Validate user_id if it's being updated
        if ($request->has('user_id')) {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
        }

        $live_video->update([
            'title' => $request->title,
            'title_ar' => $request->title_ar,
            'user_id' =>$request->user_id,
            'category_id' => $request->category_id ?? null,
            'information' => $request->information ?? null,
            'information_ar' => $request->information_ar ?? null,
            'weight' => $request->weight ?? null,
            'age_id' => $request->age_id ?? null,
            'color_id'=>$request->color_id,
            'type'=>$request->type,
            'date_barth'=>$request->date_barth,
            'animal_pen_id'=>$request->animal_pen_id,
            'start_price' => $request->start_price ?? 0,
            'address'=>$request->address,
            'age'=>$request->age,
            'age_type'=>$request->age_type,
            'terms'=>$request->terms,
            'terms_ar'=>$request->terms_ar,
            'bidding'=>$request->bidding,
            'quantity'=>$request->quantity ?? 0,
            'piece_multiplier_number' => $request->piece_multiplier_number ?? null,
            'identifier' => $request->identifier ?? null,
            'baham_count' => $request->baham_count ?? null,
            'show_partner_name' => $request->has('show_partner_name') ? true : false,
        ]);
        try {
            $firebase = new FirebaseController();
            $firebase->create_item($live_video);
        }
        catch(\Exception $t){}



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
        if($request->hasfile('health_certificate')) {
            $extension = $request->file('health_certificate')->getClientOriginalExtension();
            $newFileName = 'health_cert_' . time() . '_' . uniqid() . '.' . $extension;
            $request->file('health_certificate')->move(public_path('../storage/app/public/health_certificate/'), $newFileName);
            $health_certificate = 'health_certificate/' . $newFileName;
            $live_video->update([
                'health_certificate' => $health_certificate,
            ]);
        }
        if($request->hasfile('video')) {
            $extension = $request->file('video')->getClientOriginalExtension();
            $newFileName = 'video_' . time() . '_' . uniqid() . '.' . $extension;
            $request->file('video')->move(public_path('../storage/app/public/video/'), $newFileName);
            $video = 'video/' . $newFileName;
            $live_video->update([
                'video' => $video,
            ]);

        }




        Toastr::success(TranslationHelper::translate('Data Updated Successfully'));
        return redirect()->route('admin.products.index',$live_video->live_video_id );
    }


    public function destroy($id)
    {
        //$this->authorizable('delete video');
        $admin = LiveVideoItem::findorfail($id);
        $admin->delete();
        Toastr::success(TranslationHelper::translate('Deleted Successfully'));
        return redirect()->back();
    }
    public function comments_delete($id){
        $admin = LiveVideoComment::findorfail($id);
        $admin->delete();
        Toastr::success(TranslationHelper::translate('Deleted Successfully'));
        return redirect()->back();
    }

}
