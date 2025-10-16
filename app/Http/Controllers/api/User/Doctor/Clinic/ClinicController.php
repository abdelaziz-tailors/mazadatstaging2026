<?php

namespace App\Http\Controllers\api\User\Doctor\Clinic;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\User\Providers\BookNotRequest;
use App\Http\Requests\api\User\Providers\BookRequest;
use App\Http\Requests\api\User\Providers\MainSearchRequest;
use App\Http\Requests\api\User\Providers\SearchCityRequest;
use App\Http\Requests\api\User\Providers\SearchNameRequest;
use App\Http\Requests\api\User\Providers\SearchRequest;
use App\Http\Resources\User\ClientBookingResource;
use App\Http\Resources\User\ProviderClinicResource;
use App\Http\Resources\User\ProviderDayResource;
use App\Http\Resources\User\ProviderSearchResource;
use App\Http\Resources\User\SearchByNameResource;
use App\Models\Admin;
use App\Models\BookingNot;
use App\Models\Branch;
use App\Models\ClientBooking;
use App\Models\Clinic;
use App\Models\ClinicShiftTime;
use App\Models\Department;
use App\Models\DoctorSessionTime;
use App\Models\Notification;
use App\Models\PromoCode;
use App\Models\ProviderDay;
use Illuminate\Http\Request;

use App\Models\Category;
use App\Http\Resources\CategoryResource;

use App\Models\Banner;
use App\Http\Resources\BannerResource;

use App\Models\User\User;
use App\Http\Resources\User\ProviderResource;

use Illuminate\Support\Facades\DB;
use Kutia\Larafirebase\Facades\Larafirebase;
use TranslationHelper;
use App\Traits\ResponseTrait;

class ClinicController extends Controller
{
    use ResponseTrait;

    public function dates($id) {

        if (!auth()->guard('patients')->user()){

            return $this->failed_response(\App\Helpers\TranslationHelper::translate('Un Authenticated'));

        }


        $clinic_time =ProviderDay::where('clinic_id',$id)->get();

        $day=ProviderDayResource::collection($clinic_time);
        return $this->success_response(NULL, $day );



    }
    public function book(BookRequest $request) {

        if (!auth()->guard('patients')->user()){

            return $this->failed_response(\App\Helpers\TranslationHelper::translate('Un Authenticated'));

        }

        $date_time_id=DoctorSessionTime::find($request->date_time_id);

        if ($date_time_id->client_id){
            return $this->failed_response(\App\Helpers\TranslationHelper::translate('That Appointment is already Booked'));
        }

        $date_time_id->update(['client_id'=>auth()->guard('patients')->user()->id]);
        $total_fees =  $date_time_id->provider_day->clinic->examination_fees;

        if ($request->promo_code_id) {
            $promo_code = PromoCode::find($request->promo_code_id);
            if ( $promo_code->discount_type == 'percentage') {
                $price_after_discount = $total_fees - ($total_fees * ($promo_code->discount/100));
            }else{
                $price_after_discount = $total_fees - $promo_code->discount;
            }
            if ($price_after_discount <= 0 ) {
                $price_after_discount = 0;
            }
            $promo_code_id = $request->promo_code_id ?? null;
        }



        $clinic_time= ClientBooking::create([
            'date_time_id' => $request->date_time_id,
            'another' => $request->another,
            'patient_name' => $request->patient_name,
            'patient_phone' => $request->patient_phone,
            'insurance_company_id' => $request->insurance_company_id,
            'patient_id' => auth()->guard('patients')->user()->id,
            'clinic_id' => $date_time_id->provider_day->clinic_id ?? '',
            'user_id' => $date_time_id->provider_day->user_id  ?? '',
            'price' => $date_time_id->provider_day->clinic->examination_fees  ?? '',
            'price_after_discount' => $price_after_discount  ?? null,
            'promo_code_id' => $promo_code_id  ?? null,
        ]);
        $data= New ClientBookingResource($clinic_time);

        $fcmTokens = User::where('id',$date_time_id->provider_day->user_id)->orderBy('id', 'asc')->pluck('fcm_token')->toArray();

        $title="You have an New Appointment";
        Notification::create([
            'title' => $title,
            'description' => '',
            'url' => '',
            'type' => 'doctor',
            'status' => 'booking',
            'date_time_id' => $request->date_time_id,
            'send_to' => $clinic_time->user_id,
        ]);

        try{
            Larafirebase::withTitle($title ?? '')
                ->withBody('')
                ->withIcon('https://join.dacktra.com/dashboard/img/logo.png')
//                ->withClickAction($request->url)
                ->withAdditionalData([
                    'click_action'=> 'FLUTTER_NOTIFICATION_CLICK',
                    'screen'=> 'doctor_new_appointment_notification',
                    'booking_id'=> $request->date_time_id,
                ])
                ->sendNotification($fcmTokens ??'');
        }catch(\Exception $e){

        }
        return $this->success_response('Appointment Booked Successfully', $data );
    }
    public function bookNots(BookNotRequest  $request) {

        if (!auth()->guard('patients')->user()){

            return $this->failed_response(\App\Helpers\TranslationHelper::translate('Un Authenticated'));

        }

        $date_time_id=BookingNot::where('client_booking_id',$request->book_id)->first();

        if ($date_time_id){
            return $this->failed_response(\App\Helpers\TranslationHelper::translate('Not already Added'));
        }

        $documents = [];

        if($request->hasfile('documents')) {

            foreach ($request->file('documents') as $image) {
                $name = 'booking/documents/'.rand(11111, 99999) .'_'.$image->getClientOriginalName();
                $image->move(public_path('../storage/app/public/booking/documents/'), $name);
                $documents[] = $name;

            }

        }
        $images = [];

        if($request->hasfile('images')) {

            foreach ($request->file('images') as $image_data) {
                $name_image = 'booking/image/'.rand(11111, 99999) .'_'.$image_data->getClientOriginalName();
                $image_data->move(public_path('../storage/app/public/booking/documents/'), $name_image);
                $images[] = $name_image;

            }

        }


        $clinic_time=BookingNot::create([
            'client_booking_id' => $request->book_id,
            'patient_id' => auth()->guard('patients')->user()->id,
            'gender_id' => $request->gender,
            'age' => $request->age,
            'symptoms' => $request->symptoms,
            'documents' => json_encode($documents),
            'images' => json_encode($images),
        ]);



        return $this->success_response('Added Successfully', '' );
    }






    public function bookList() {

        if (!auth()->guard('patients')->user()){

            return $this->failed_response(\App\Helpers\TranslationHelper::translate('Un Authenticated'));

        }


        $clinic_time =ClientBooking::where('patient_id',auth()->guard('patients')->user()->id)->orderBy('id','Desc')
            ->paginate(10);


        $day=ClientBookingResource::collection($clinic_time);

        return response()->json(['success' => true, 'code' => 200, 'message' => null,
            'data' => $day,
            'pagination' => [
                'total' => $clinic_time->total(),
                'count' => $clinic_time->count(),
                'per_page' => $clinic_time->perPage(),
                'current_page' => $clinic_time->currentPage(),
                'total_pages' => $clinic_time->lastPage(),
                'links' => [
                    'prev' => $clinic_time->previousPageUrl(),
                    'next' => $clinic_time->nextPageUrl(),
                ],
            ],
        ]);

//        return $this->success_response(NULL, $day );







    }
    public function bookCancel(Request $request) {

        if (!auth()->guard('patients')->user()){

            return $this->failed_response(\App\Helpers\TranslationHelper::translate('Un Authenticated'));

        }


        $clinic_time =ClientBooking::where('id',$request->book_id)->where('patient_id',auth()->guard('patients')->user()->id)->first();
        if (!$clinic_time){
            return $this->failed_response(\App\Helpers\TranslationHelper::translate('No Appointment Found'));
        }




        $seesion_time=DoctorSessionTime::find($clinic_time->date_time_id);
        $seesion_time->update(['client_id'=>null]);
        $clinic_time->update(['cancel_by'=>'patient','cancel'=>1]);

        $fcmTokens = User::where('id',$clinic_time->user_id)->orderBy('id', 'asc')->pluck('fcm_token')->toArray();

        $title="Patient has Cancel the Appointment";
//        dd($fcmTokens,$clinic_time->date_time_id);
        Notification::create([
            'title' => $title,
            'description' => '',
            'url' => '',
            'type' => 'doctor',
            'status' => 'booking',
            'date_time_id' => $clinic_time->date_time_id,
            'send_to' => $clinic_time->user_id,
        ]);

        try{
            Larafirebase::withTitle($title ?? '')
                ->withBody('')
                ->withIcon('https://join.dacktra.com/dashboard/img/logo.png')
//                ->withClickAction($request->url)
                ->withAdditionalData([
                    'click_action'=> 'FLUTTER_NOTIFICATION_CLICK',
                    'screen'=> 'doctor_cancel_appointment_notification',
                    'booking_id'=> $clinic_time->date_time_id,
                ])
                ->sendNotification($fcmTokens ??'');


        }catch(\Exception $e){

        }




        return $this->success_response(\App\Helpers\TranslationHelper::translate(' Book Appointment Delete Successfully '), '' );



    }
    public function approvedChangeTime(Request $request) {

        if (!auth()->guard('patients')->user()){

            return $this->failed_response(\App\Helpers\TranslationHelper::translate('Un Authenticated'));

        }


        $clinic_time =ClientBooking::where('id',$request->book_id)->where('patient_id',auth()->guard('patients')->user()->id)->first();
        if (!$clinic_time){
            return $this->failed_response(\App\Helpers\TranslationHelper::translate('No Appointment Found'));
        }




        $clinic_time->update(['change_time'=>null]);

        $fcmTokens = User::where('id',$clinic_time->user_id)->orderBy('id', 'asc')->pluck('fcm_token')->toArray();

        $title="Patient has Approved the Appointment New Time";
//        dd($fcmTokens,$clinic_time->date_time_id);
        Notification::create([
            'title' => $title,
            'description' => '',
            'url' => '',
            'type' => 'doctor',
            'status' => 'booking',
            'date_time_id' => $clinic_time->date_time_id,
            'send_to' => $clinic_time->user_id,
        ]);

        try{
            Larafirebase::withTitle($title ?? '')
                ->withBody('')
                ->withIcon('https://join.dacktra.com/dashboard/img/logo.png')
//                ->withClickAction($request->url)
                ->withAdditionalData([
                    'click_action'=> 'FLUTTER_NOTIFICATION_CLICK',
                    'screen'=> 'doctor_cancel_appointment_notification',
                    'booking_id'=> $clinic_time->date_time_id,
                ])
                ->sendNotification($fcmTokens ??'');


        }catch(\Exception $e){

        }




        return $this->success_response(\App\Helpers\TranslationHelper::translate(' Book Appointment Time Approved Successfully '), '' );



    }


}
