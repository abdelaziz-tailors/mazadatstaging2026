<?php

namespace App\Http\Requests\api\Video;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Auth;
class AuctionAwardVideoRequest extends FormRequest
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
            'title' => 'required',
            'category_id' => 'required',
            'image'=> ' nullable',
             'image.*'=> 'mimes:jpeg,jpg,png|max:20000',
//            'image_video'=> 'mimes:mp4,ogx,oga,ogv,ogg,webm,jpeg,jpg,png|max:20000',
            'information' => 'required|string|max:255',
            'weight' => 'required',
            'age' => 'required',
            'auction_time' => 'required|date_format:Y-m-d H:i',
            'start_price' => 'required',
            'bidding' => 'required',
            'quantity' => 'required',

        ];
    }

    public function messages()
    {
        return [
            'file_type.required' => TranslationHelper::translate('please  enter file type'),
            'file.required' => TranslationHelper::translate('please  Add  file'),
//            'hashtag.string' => TranslationHelper::translate('hashtag  Add  file'),
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
