<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FirebaseController;
use App\Models\Age;
use App\Models\AnimalPen;
use App\Models\Category;
use App\Models\Color;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use Illuminate\Http\Request;
use App\Traits\ActionTrait;

use Brian2694\Toastr\Facades\Toastr;

use App\Helpers\TranslationHelper;
use App\Http\Requests\Dashboard\Admin\ChangeHospitalPasswordRequest;
use App\Http\Requests\Dashboard\Providers\StoreProviderRequest;
use App\Http\Requests\Dashboard\Providers\UpdateProviderRequest;
use App\Models\Country;
use Yajra\DataTables\DataTables;

use App\Traits\AuthorizeTrait;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use AuthorizeTrait ,ActionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        return view('dashboard.pages.orders.index',compact('request'));
    }


    // get index data by ajax
    public function get_data (Request $request) {
        // dd($re/)




        $providers = LiveVideoItem::whereNotNull('user_finished_id');


        return Datatables::of($providers)

            ->editColumn('title', function(LiveVideoItem $item) {
                return app()->getLocale() === 'ar' ? $item->title_ar : $item->title;
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
            ->addColumn('status', function(LiveVideoItem $item) {
//                return  TranslationHelper::translate($item->status);
                return view('dashboard.pages.orders.status')
                    ->with(['item' => $item]);

            })
            ->addColumn('shipping_address', function(LiveVideoItem $item) {
                return $item->addressData->address ?? '';
            })

            ->addColumn('city', function(LiveVideoItem $item) {
                return $item->addressData->city->name ?? '';
            })

            ->addColumn('finished_price', function(LiveVideoItem $item) {
                return  $item->finished_price;
            })
            ->addColumn('buyer', function(LiveVideoItem $item) {
                return $item->user_auction->name ?? '';
            })
            ->addColumn('payment_proof', function(LiveVideoItem $item) {
                return view('dashboard.pages.orders.payment_proof_column')->with(['item' => $item]);
            })

            ->addColumn('action', function(LiveVideoItem $item) {
                return view('dashboard.pages.orders.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'phone','email', 'status', 'payment_proof', 'action'])
            ->startsWithSearch()
            -> make(true);
    }
    function show($id){
        $video=LiveVideo::find($id);
        return view('dashboard.pages.videos.show',compact('video'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create($id)
    {

        //$this->authorizable('add video');
        $ages = Age::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
        $colors = Color::select('id','color', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
        $categories = Category::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
        $animal_pens = AnimalPen::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->get();
        $providers = User::where('user_type','vendor')->get();
        $live_video = LiveVideo::find($id);

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


        $request->validate([
            // 'title' => 'required',
            // 'title_ar' => 'required',
            'user_id' => 'required|exists:users,id',
            // 'category_id' => 'required',
            // 'information' => 'required',
            // 'information_ar' => 'required',
            'weight' => 'required',
            'age' => 'required',
            // 'color_id' => 'required',
            // 'type' => 'required',
            // 'date_barth' => 'required',
            // 'animal_pen_id' => 'required',
            // 'start_price' => 'required',
            // 'health_certificate' => 'required',
            'video' => 'sometimes|file|mimes:mp4,avi,wmv,flv',
            // 'address' => 'required',
            // 'age' => 'required',
            // 'age_type' => 'required',
            // 'terms' => 'required',
            // 'terms_ar' => 'required',
            // 'bidding' => 'required',
            'health_certificate' => 'sometimes|file|mimes:pdf,jpg,jpeg,png',
        ]);






        $file = [];

        if($request->hasfile('image')) {

            foreach ($request->file('image') as $image) {
                $name = 'image_video/'.rand(11111, 99999) .'_'.$image->getClientOriginalName();
                $image->move(public_path('../storage/app/public/image_video/'), $name);
                $file[] = $name;
            }
        }

        if($request->hasfile('health_certificate')) {
            $health_certificate = $request->file('health_certificate')->getClientOriginalName();
            $request->file('health_certificate')->move(public_path('../storage/app/public/health_certificate/'), $health_certificate);
            $health_certificate = 'health_certificate/'.$health_certificate;
        }


        if($request->hasfile('video')) {
            $video = $request->file('video')->getClientOriginalName();
            $request->file('video')->move(public_path('../storage/app/public/video/'), $video);
            $video = 'video/'.$video;
        }


        $add_iteam=LiveVideoItem::create([
            'live_video_id' => $request->video_id,
            'title' => $request->title,
            'title_ar' => $request->title_ar,
            'status'=>'pending',
            'user_id' =>$request->user_id,
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
        ]);


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

        return view('dashboard.pages.orders.edit', compact(['providers','data','categories','colors','ages','animal_pens','live_video']));

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


        if($request->payment_status == 'paid' && $request->status_cart == 'pending'){

            $request['status_cart']='confirmed';
        }
        // dd($request->all());
            $live_video->update([
                'payment_status'=>$request->payment_status,
                'status_cart'=>$request->status_cart,

        ]);






        Toastr::success(TranslationHelper::translate('Data Updated Successfully'));
        return redirect()->route('admin.orders.index' );
    }


    public function destroy($id)
    {
        //$this->authorizable('delete video');
        $admin = LiveVideoItem::findorfail($id);
        $admin->delete();
        Toastr::success(TranslationHelper::translate('Deleted Successfully'));
        return redirect()->back();
    }

}
