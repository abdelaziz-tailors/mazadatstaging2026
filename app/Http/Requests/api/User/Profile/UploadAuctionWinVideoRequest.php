<?php

namespace App\Http\Requests\api\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;

class UploadAuctionWinVideoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'video' => 'required|file|max:102400',
        ];
    }

    public function messages()
    {
        return [
            'video.required' => TranslationHelper::translate('Please choose a video'),
            'video.file' => TranslationHelper::translate('Invalid file upload'),
            'video.max' => TranslationHelper::translate('Video max size 100 MB'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 200,
            'success' => false,
            'message' => $validator->errors()->first()
        ]));
    }
}
