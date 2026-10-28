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
        $this->authorizable('view notifications');
        $country = Notification::whereNull('user_id')->select('id','title', 'description', 'created_at')->get();
        return Datatables::of($country)
            ->editColumn('description', function(Notification $item) {
                $description = $item->description ?? '';
                return \Illuminate\Support\Str::limit($description, 60);
            })
            ->editColumn('created_at', function(Notification $item) {
                return optional($item->created_at)->format('Y-m-d');
            })
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

        // Keep the existing in-app notification behavior.
        $notification = Notification::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        // All user types: buyers, sellers and partners.
        // Remove empty and duplicate device tokens.
        $tokens = User::whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->map(function ($token) {
                return trim((string) $token);
            })
            ->filter(function ($token) {
                return $token !== '';
            })
            ->unique()
            ->values();

        $sent = 0;
        $failed = 0;

        $data = [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'screen' => 'notification',
        ];

        if ($request->filled('url')) {
            $data['url'] = (string) $request->input('url');
        }

        if ($tokens->isNotEmpty()) {
            try {
                $fcm = new \App\Services\FCMService();

                foreach ($tokens as $token) {
                    try {
                        $result = $fcm->sendToDevice(
                            $token,
                            (string) $request->title,
                            (string) $request->description,
                            $data
                        );

                        if ($result['success'] ?? false) {
                            $sent++;
                        } else {
                            $failed++;

                            $reason = (string) (
                                $result['error'] ?? 'Unknown Firebase error'
                            );

                            \Illuminate\Support\Facades\Log::warning(
                                'SUPERADMIN_PUSH_DEVICE_FAILED',
                                [
                                    'notification_id' => $notification->id,
                                    'device_ref' => substr(
                                        hash('sha256', $token),
                                        0,
                                        12
                                    ),
                                    'reason' => str_replace(
                                        $token,
                                        '[REDACTED]',
                                        $reason
                                    ),
                                ]
                            );
                        }
                    } catch (\Throwable $e) {
                        $failed++;

                        \Illuminate\Support\Facades\Log::error(
                            'SUPERADMIN_PUSH_DEVICE_EXCEPTION',
                            [
                                'notification_id' => $notification->id,
                                'device_ref' => substr(
                                    hash('sha256', $token),
                                    0,
                                    12
                                ),
                                'exception' => get_class($e),
                                'reason' => str_replace(
                                    $token,
                                    '[REDACTED]',
                                    $e->getMessage()
                                ),
                            ]
                        );
                    }
                }
            } catch (\Throwable $e) {
                $failed = $tokens->count();

                \Illuminate\Support\Facades\Log::error(
                    'SUPERADMIN_PUSH_SETUP_FAILED',
                    [
                        'notification_id' => $notification->id,
                        'exception' => get_class($e),
                        'reason' => $e->getMessage(),
                    ]
                );
            }
        }

        \Illuminate\Support\Facades\Log::info(
            'SUPERADMIN_PUSH_SUMMARY',
            [
                'notification_id' => $notification->id,
                'unique_devices' => $tokens->count(),
                'accepted' => $sent,
                'failed' => $failed,
            ]
        );

        if ($tokens->isEmpty()) {
            Toastr::warning(
                'تم حفظ الإشعار، لكن لا توجد أجهزة مسجلة لاستقبال التنبيهات'
            );
        } elseif ($failed > 0) {
            Toastr::warning(
                "تم حفظ الإشعار. Firebase قبل {$sent} إشعار وفشل {$failed}. راجع السجل."
            );
        } else {
            Toastr::success(
                "تم حفظ الإشعار وقبل Firebase الإرسال إلى {$sent} جهاز"
            );
        }

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
        $notification = Notification::whereNull('user_id')->findorfail($id);
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
        $notification = Notification::whereNull('user_id')->findorfail($id);
        $notification->delete();
        Toastr::success(TranslationHelper::translate('Notification Deleted Successfully'));
        return redirect()->route('admin.notifications.index');
    }

}
