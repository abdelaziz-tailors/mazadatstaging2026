<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Brian2694\Toastr\Facades\Toastr;
use App\Helpers\TranslationHelper;

use App\Http\Requests\Dashboard\Admin\ChangeHospitalPasswordRequest;
use App\Http\Requests\Dashboard\Admin\UpdateProfileRequest;


class AdminProfileController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        return view('dashboard.pages.admin-profile.profile', compact(['admin']));
    }

    public function update_profile(UpdateProfileRequest $request)
    {
        $admin = Auth::guard('admin')->user();
        if ($request->hasFile('image') && $admin->image != 'admins/default.png' && $admin->image != NULL) {
            Storage::disk('public')->delete($admin->image);
        }
        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'image' => ($request->hasFile('image')) ? Storage::disk('public')->putFile('admins', $request->file('image')) : $admin->image
        ]);
        Toastr::success(TranslationHelper::translate('Profile Updated Successfully'));
        return redirect()->route('admin.dashboard.index');
    }

    public function change_password_form()
    {
        $admin = Auth::guard('admin')->user();
        return view('dashboard.pages.admin-profile.change_password', compact(['admin']));
    }
    public function save_password(ChangeHospitalPasswordRequest $request)
    {
        $admin = Auth::guard('admin')->user();
        $admin->update([
            'password' => bcrypt($request->password)
        ]);
        Toastr::success(TranslationHelper::translate('Password Updated Successfully'));
        return redirect()->route('admin.dashboard.index');
    }

}
