<?php

namespace App\Http\Requests\api\User\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Auth;
class GiftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'live_id' => 'required|exists:live_videos,id',
            'gift_id' => 'required|exists:gifts,id',

        ];
    }

    public function messages()
    {
        return [
            'live_id.required' => TranslationHelper::translate('Please choose Live video'),
            'live_id.exists' => TranslationHelper::translate('Live video not Exists'),
            'gift_id.required' => TranslationHelper::translate('Please choose gift'),
            'gift_id.exists' => TranslationHelper::translate('gift not Exists'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 200,
            'success'   => false,
            'message'   => $validator->errors()->first()
        ]));
    }
}
