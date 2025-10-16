<?php

namespace App\Http\Requests\api\Video;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Auth;
class AddVideoReplayCommentRequest extends FormRequest
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
            'comment_id'=> 'required|exists:video_comments,id,deleted_at,NULL',

        ];
    }

    public function messages()
    {
        return [
            'comment.required' => TranslationHelper::translate('please  add Your comment'),
            'comment_id.required' => TranslationHelper::translate('please  Add  comment id '),
            'comment_id.exists' => TranslationHelper::translate('comment Not Exist '),
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
