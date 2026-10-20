<?php

namespace App\Http\Requests\api\Video;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Auth;
class AddVideoRequest extends FormRequest
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
        // Match the subscriber eligibility check in LiveVideoController::add.
        $admin = auth('api')->user()->admin ?? null;
        $presence = $admin && $admin->partner !== 'partner' ? 'required' : 'nullable';
        return [
            'title_ar' => 'required',
            'start_price' => 'required|numeric|min:0',
            'information_ar' => 'required|string|max:255',
            'date_start_at' => 'required|date_format:Y-m-d',
            'date_end_at' => 'required|date_format:Y-m-d',
            'time_start_at' => 'required|date_format:H:i',
            'time_end_at' => 'required|date_format:H:i',
            'city_id' => 'nullable|exists:cities,id',
            'type' => 'nullable',
            'image' => 'nullable|array',
            'image.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'terms_conditions' => 'nullable|string|max:255',
            'video_type' => 'nullable|in:live,recorded,photo',
            'partners_type'=>'nullable',
            'partner_id'=>'nullable',
            'commission_amount' => $presence.'|numeric|min:0',
            'commission_payer' => $presence.'|in:buyer,seller',
            'service_fee' => 'nullable|min:0',
            'title' => 'nullable',
            'information' => 'nullable|string|max:255',




        ];
    }

    public function messages()
    {
        return [
            'commission_amount.required' => trans('validation.auction_commission_required'),
            'commission_amount.numeric' => trans('validation.auction_commission_numeric'),
            'commission_amount.min' => trans('validation.auction_commission_min'),
            'commission_payer.required' => trans('validation.auction_payer_required'),
            'commission_payer.in' => trans('validation.auction_payer_in'),
            'title.required' => TranslationHelper::translate('please  enter title'),
            'title_ar.required' => TranslationHelper::translate('please  enter title'),
            'information.required' => TranslationHelper::translate('please  enter information'),
            'information_ar.required' => TranslationHelper::translate('please  enter information'),
            'date_start_at.required' => TranslationHelper::translate('please  enter date of star '),
            'date_end_at.required' => TranslationHelper::translate('please  enter date of end '),
            'time_start_at.required' => TranslationHelper::translate('please  enter Time of star '),
            'time_end_at.required' => TranslationHelper::translate('please  enter Time of end '),
            'terms_conditions.required' => TranslationHelper::translate('please  enter terms and conditions '),
            'terms_conditions_ar.required' => TranslationHelper::translate('please  enter terms and conditions '),
            'partner_id.requiredif' => TranslationHelper::translate('please enter partner id '),
            'video_type.required' => TranslationHelper::translate('please enter video type '),
            'partners_type.required' => TranslationHelper::translate('please enter partners type '),
            'image.required' => TranslationHelper::translate('please enter image '),
            'image.*.mimes' => TranslationHelper::translate('The image must be a file of type: jpeg, jpg, png.'),
            'image.*.max' => TranslationHelper::translate('The image size must not exceed 20MB.'),
            'city_id.required' => TranslationHelper::translate('please Add city'),

        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 200,
            'success'   => false,
            'message'   => $validator->errors()->first(),
            'errors' => $validator->errors()->toArray(),
        ]));
    }
}
