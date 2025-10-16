<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'App\Http\Controllers\api', 'middleware' => ['APISettings']], function() {


    Route::group(['namespace' => 'Shared'], function () {

        Route::get('gifts', 'GiftController');
        Route::get('cities', 'StateController');
        Route::get('packages', 'PackageController');
        Route::get('sounds', 'SoundController');
        Route::get('reports', 'ReportController');
        Route::get('user-reports', 'ReportController@userReports');

        Route::get('about', 'PageController@about');
        Route::get('privacy', 'PageController@privacy');
        Route::get('terms', 'PageController@terms');
        Route::get('category', 'CategoryController');
        Route::get('partners', 'PartnerController');
        Route::get('colors', 'ColorController');
        Route::get('bank-payment', 'SettingController@paymentMethods');
        Route::get('contact', 'SettingController@contact');

    });

    // 1. User App
    include('api/user.php');
    // 2. Provider App
    // 3. Driver App
});
