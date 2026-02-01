<?php

namespace App\Http\Controllers\api\Shared;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AgeResource;
use App\Models\Age;
use App\Traits\ResponseTrait;

class AgeController extends Controller
{
    use ResponseTrait;

    public function __invoke() {
        // Get all active ages (managed from admin panel)
        // Soft-deleted ages are automatically excluded by the SoftDeletes trait
        $ages = Age::select('id', 'name->'.app()->getLocale().' as name')
            ->where('is_active', 1)
            ->orderBy('id', 'asc')
            ->get();
        $data = AgeResource::collection($ages);
        return $this->success_response(TranslationHelper::translate('Successfully'), $data);
    }
}

