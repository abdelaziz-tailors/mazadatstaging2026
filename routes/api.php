<?php

use App\Http\Controllers\NafathController;
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
        Route::get('sliders', 'SliderController');

        Route::get('about', 'PageController@about');
        Route::get('privacy', 'PageController@privacy');
        Route::get('terms', 'PageController@terms');
        Route::get('category', 'CategoryController');
        Route::get('partners', 'PartnerController');
        Route::get('colors', 'ColorController');
        Route::get('ages', 'AgeController');
        Route::get('bank-payment', 'SettingController@paymentMethods');
        Route::get('contact', 'SettingController@contact');
        Route::post('contact', 'ContactController@store');

        // Roles
        Route::apiResource('roles', 'RoleController');

    });

    // 1. User App
    include('api/user.php');
    // 2. Provider App
    // 3. Driver App



});
// Nafath
Route::prefix('nafath')->group(function () {
    Route::post('request', [NafathController::class, 'sendRequest']);
    Route::post('request/status', [NafathController::class, 'getStatus']);

    // Nafath callback
    Route::post('callback', [NafathController::class, 'callback']);

});
