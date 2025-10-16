<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Models\UserReport;
use Illuminate\Http\Request;

use App\Models\City;
use App\Http\Resources\CityResource;

use App\Traits\ResponseTrait;

class ReportController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $cities = Report::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)
            ->get();
        $data = ReportResource::collection($cities);
        return $this->success_response(NULL, $data);
    }
    public function userReports() {
        $cities = UserReport::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)
            ->get();
        $data = ReportResource::collection($cities);
        return $this->success_response(NULL, $data);
    }
}
