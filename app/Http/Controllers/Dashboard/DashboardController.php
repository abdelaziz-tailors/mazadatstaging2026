<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Department;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Patient;
use Illuminate\Http\Request;
use App\Helpers\TranslationHelper;

use App\Models\Admin;
use App\Models\category;
use App\Models\User\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index() {


        if (Auth::guard('admin')->user()->type=="admin") {

            $admins = Admin::where('type','admin')->count();
            $partner = Admin::where('type','partner')->count();
            $user = User::where('user_type','!=','vendor')->count();
            $vendor = User::where('user_type','vendor')->count();
            $categories = category::count();
            $LiveVideo = LiveVideo::count();
            $LiveVideoItem = LiveVideoItem::count();
            $sales = LiveVideoItem::sum('finished_price');

            $reports = array();
            $tables = array();
            $reports[] = array('name'=>TranslationHelper::translate('admins'), 'value'=>$admins, 'color'=>'primary', 'icon'=>'fa-solid fa-user-tie');
            $reports[] = array('name'=>TranslationHelper::translate('Users'), 'value'=>$user, 'color'=>'success',  'icon'=>'fa-solid fa-user');
            $reports[] = array('name'=>TranslationHelper::translate('Vendors'), 'value'=>$vendor, 'color'=>'danger',  'icon'=>'fa-solid fa-user-tie');
            $reports[] = array('name'=>TranslationHelper::translate('Partners'), 'value'=>$partner, 'color'=>'warning',  'icon'=>'fa-solid fa-user');
            $reports[] = array('name'=>TranslationHelper::translate('Categories'), 'value'=>$categories, 'color'=>'info',  'icon'=>'fa-solid fa-list');
            $reports[] = array('name'=>TranslationHelper::translate('Auctions'), 'value'=>$LiveVideo, 'color'=>'dark',  'icon'=>'fa-solid fa-video');
            $reports[] = array('name'=>TranslationHelper::translate('Auctions Product'), 'value'=>$LiveVideoItem, 'color'=>'dark',  'icon'=>'fa-solid fa-list');
            $reports[] = array('name'=>TranslationHelper::translate('Sales'), 'value'=>$sales, 'color'=>'success',  'icon'=>'fa-solid fa-coins');




            $tables['latest_doctors'] = User::orderBy('id','desc')->limit(5)->get();
            $tables['latest_users'] = User::orderBy('id','desc')->limit(5)->get();

            //

        }else{

            $vendor = User::where('admin_id',Auth::guard('admin')->user()->id)->where('user_type','vendor')->count();
            $categories = Category::where('admin_id',Auth::guard('admin')->user()->id)->count();
            $LiveVideo = LiveVideo::where('admin_id',Auth::guard('admin')->user()->id)->count();
            $LiveVideoItem = LiveVideoItem::whereHas('videoLive', function($query) {
                $query->where('admin_id',Auth::guard('admin')->user()->id);
            })->count();

            $sales = LiveVideoItem::whereHas('videoLive', function($query) {
                $query->where('admin_id',Auth::guard('admin')->user()->id);
            })->sum('finished_price');

            $reports = array();
            $tables = array();
            $reports[] = array('name'=>TranslationHelper::translate('Vendors'), 'value'=>$vendor, 'color'=>'danger',  'icon'=>'fa-solid fa-user-tie');
            $reports[] = array('name'=>TranslationHelper::translate('Categories'), 'value'=>$categories, 'color'=>'info',  'icon'=>'fa-solid fa-list');
            $reports[] = array('name'=>TranslationHelper::translate('Auctions'), 'value'=>$LiveVideo, 'color'=>'dark',  'icon'=>'fa-solid fa-video');
            $reports[] = array('name'=>TranslationHelper::translate('Auctions Product'), 'value'=>$LiveVideoItem, 'color'=>'dark',  'icon'=>'fa-solid fa-list');
            $reports[] = array('name'=>TranslationHelper::translate('Sales'), 'value'=>$sales, 'color'=>'success',  'icon'=>'fa-solid fa-coins');


        }


        return view('dashboard.home', compact(['reports','tables']));

    }
    public function appointment(){

        return view('dashboard.appointment');


    }
}
