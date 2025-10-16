<?php

namespace App\Traits;
use Illuminate\Support\Facades\Auth;
use App\Models\User\User;

trait ResponseTrait
{
    public function success_response ($message = NULL, $data = []) {
        return response()->json(['success' => true, 'code' => 200, 'message' => $message, 'data' => $data]);
    }
    public function failed_response ($message = NULL, $code = 200) {
        return response()->json(['success' => false, 'code' => $code, 'message' => $message], $code);
    }

    public function success_notcompleted_response ($message = NULL, $data = []) {
        return response()->json(['success' => false, 'code' => 200, 'message' => $message, 'data' => $data]);
    }

}
