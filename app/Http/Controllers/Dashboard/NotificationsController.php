<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Notification\StoreNotificationRequest;
use App\Models\Notification;
use App\Models\NursingCompany;
use App\Models\Patient;
use App\Models\User\User;
use Illuminate\Http\Request;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;

use App\Helpers\TranslationHelper;
use Kutia\Larafirebase\Facades\Larafirebase;
use Yajra\DataTables\DataTables;

use App\Traits\AuthorizeTrait;
use App\Traits\ActionTrait;
use App\Models\Country;

use App\Http\Requests\Dashboard\Country\StoreCountryRequest;
use App\Http\Requests\Dashboard\Country\UpdateCountryRequest;

class NotificationsController extends Controller
{
    use AuthorizeTrait, ActionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->authorizable('view notifications');
        return view('dashboard.pages.notifications.index');
    }

    // get index data by ajax
    public function get_data (Request $request) {
        $country = Notification::select('id','title')->get();
        return Datatables::of($country)

            ->addColumn('action', function(Notification $item) {
                return view('dashboard.pages.notifications.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'title', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->authorizable('add notification');
        return view('dashboard.pages.notifications.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Country\StoreCountryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreNotificationRequest $request)
    {
        $this->authorizable('add notification');
        Notification::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);
            $fcmTokens = User::whereNotNull('fcm_token')->orderBy('id', 'asc')->pluck('fcm_token')->toArray();
        try{
            Larafirebase::withTitle($request->title ?? '')
                ->withBody($request->description ?? '')
                ->withIcon('https://vlog.dev03.matrix-clouds.com/dashboard/img/logo-white.png')
                ->withClickAction($request->url)
                ->withAdditionalData([
                    'click_action'=> 'FLUTTER_NOTIFICATION_CLICK',
                    'screen'=> 'notification',
                ])
                ->sendNotification($fcmTokens ??'');


        }catch(\Exception $e){

        }

        Toastr::success(TranslationHelper::translate('New Notification Created Successfully'));
        return redirect()->route('admin.notifications.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function view($id)
    {
        $this->authorizable('edit notification');
        $notification = Notification::findorfail($id);
        return view('dashboard.pages.notifications.edit', compact(['notification']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\Country\UpdateCountryRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCountryRequest $request, $id)
    {
        return redirect()->route('admin.notifications.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorizable('delete notification');
        $notification = Notification::findorfail($id);
        $notification->delete();
        Toastr::success(TranslationHelper::translate('Notification Deleted Successfully'));
        return redirect()->route('admin.notifications.index');
    }

}
