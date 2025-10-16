<?php

namespace App\Http\Requests\api\Video\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Auth;
class AddUserReportRequest extends FormRequest
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
            'report_id' => 'nullable|exists:user_reports,id,deleted_at,NULL',
            'comment' => 'required_if:report_id,null',

        ];
    }

    public function messages()
    {
        return [
            'report_id.exists' => TranslationHelper::translate('Selected report Not Exist'),
            'comment.required' => TranslationHelper::translate('please  Add  your report'),
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
