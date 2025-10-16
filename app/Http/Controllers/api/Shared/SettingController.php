<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Models\Setting;
use App\Models\UserReport;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    use ResponseTrait;

    public function paymentMethods() {
        $data = Setting::select('bank_name',
        'branch_name',
        'iban',
        'swift_code',
        'bank_account_number',
        'bank_account_name',)
            ->first();
        return $this->success_response(NULL, $data);
    }
    public function contact() {
        $data = Setting::select('phone', 'whatsapp', 'facebook', 'instagram', 'tiktok', 'logo')
            ->first();

            $data->logo = Storage::disk('public')->url($data->logo);
        return $this->success_response(NULL, $data);
    }
}
