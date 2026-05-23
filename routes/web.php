<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TelrPaymentController;
use App\Http\Controllers\Web\DeepLinkAssociationController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\SharedProfileController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/payment', [TelrPaymentController::class, 'showForm']);
Route::post('/payment/initiate', [TelrPaymentController::class, 'initiatePayment']);
Route::get('/payment/callback', [TelrPaymentController::class, 'handleCallback']);
Route::get('/payment/callback', [TelrPaymentController::class, 'handleCallback'])->name('payment.callback');

// Public Pages Routes
Route::get('/privacy', [PageController::class, 'privacy'])->name('web.privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('web.terms');
Route::get('/about', [PageController::class, 'about'])->name('web.about');
Route::get('/.well-known/assetlinks.json', [DeepLinkAssociationController::class, 'assetLinks'])
    ->name('deep-links.assetlinks');
Route::get('/apple-app-site-association', [DeepLinkAssociationController::class, 'appleAppSiteAssociation'])
    ->name('deep-links.apple-association');
Route::get('/.well-known/apple-app-site-association', [DeepLinkAssociationController::class, 'appleAppSiteAssociation'])
    ->name('deep-links.apple-association.well-known');
Route::get('/u/{id}', [SharedProfileController::class, 'show'])
    ->whereNumber('id')
    ->name('shared.profile');

Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
    'namespace' => 'App\Http\Controllers\Website',
], function () {
});

Route::get('/', [HomeController::class, 'index'])->name('dashboard.index');


Route::namespace('Front')->group(function () {
});

