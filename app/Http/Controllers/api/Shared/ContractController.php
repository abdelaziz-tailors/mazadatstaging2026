<?php

namespace App\Http\Controllers\api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobResource;
use App\Models\Contract;
use App\Models\JobTitle;
use Illuminate\Http\Request;

use App\Models\City;
use App\Http\Resources\CityResource;

use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        $contract_pdf = Contract::where('id', '93')->first();
        $data=[
            'contract_pdf'=>Storage::disk('public')->url($contract_pdf->pdf)
        ];
        return $this->success_response(NULL, $data);
    }
    public function lab() {
        $contract_pdf = Contract::where('id', '94')->first();
        $data=[
            'contract_pdf'=>Storage::disk('public')->url($contract_pdf->pdf)
        ];
        return $this->success_response(NULL, $data);
    }
}
