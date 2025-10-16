<?php

namespace App\Http\Requests\api\Video;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Auth;
class AddVideoCommentRequest extends FormRequest
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
            'comment'=> 'required',
            'live_video_item_id'=> 'required|exists:live_video_items,id,deleted_at,NULL',

        ];
    }

    public function messages()
    {
        return [
            'comment.required' => TranslationHelper::translate('please  add Your comment'),
            'live_video_item_id.required' => TranslationHelper::translate('please  Add  video item id'),
            'live_video_item_id.exists' => TranslationHelper::translate('video item Not Exist '),
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
