<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Models\BalanceRequest;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Traits\ActionTrait;
use App\Traits\AuthorizeTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class SettingController extends Controller
{

    use AuthorizeTrait, ActionTrait;


    public function edit()
    {
        $settings = Setting::first();
        return view('dashboard.pages.settings.edit', compact('settings'));
    }


    public function update(Request $request)
    {
        $settings = Setting::first();

        // $request->validate([
        //     'price' => 'required|numeric',
        //     'currency' => 'required|string',
        // ]);


        // dd($request->all());

        if($settings){
            $settings->update($request->all());
        }else{
            Setting::create($request->all());
        }





        if($request->hasFile('logo')){
            $settings->logo = Storage::disk('public')->putFile('settings', $request->file('logo'));
            $settings->save();
        }

        Toastr::success(TranslationHelper::translate('Settings Updated Successfully'));
        return redirect()->back();
    }



    //
}
