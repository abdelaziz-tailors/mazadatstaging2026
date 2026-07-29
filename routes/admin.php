<?php
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\AdminController;
use App\Http\Controllers\Dashboard\AdminProfileController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\AdminAuth\LoginController;
use App\Http\Controllers\Dashboard\GiftController;
use App\Http\Controllers\Dashboard\SoundController;
use App\Http\Controllers\Dashboard\ReportController;
use App\Http\Controllers\Dashboard\NotificationsController;
use App\Http\Controllers\Dashboard\PackageController;
use App\Http\Controllers\Dashboard\PageController;
use App\Http\Controllers\Dashboard\ReportUserController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\VendorController;
use App\Http\Controllers\Dashboard\VideoController;
use App\Http\Controllers\Dashboard\CityController;
use App\Http\Controllers\Dashboard\ColorController;
use App\Http\Controllers\Dashboard\AgeController;
use App\Http\Controllers\Dashboard\AnimalPenController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\PartnerController;
use App\Http\Controllers\Dashboard\AuctionController;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\OrderPieceServiceController;
use App\Http\Controllers\Dashboard\ItemServiceController;
use App\Http\Controllers\Dashboard\SettingController;
use App\Http\Controllers\Dashboard\SliderController;
use App\Http\Controllers\Dashboard\UserSubscriptionController;
use App\Http\Controllers\Dashboard\SellerSubmissionController;
use App\Http\Controllers\Dashboard\ContactMessageController;
use App\Http\Controllers\Dashboard\PartnerFinanceController;

Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeViewPath', 'dashboardLocale']], function () {
    Route::group(['prefix' => 'admin', 'as'=>'admin.'], function () {

        Route::group(['middleware'=>'AuthAdmin'], function () {

            // Dashboard Home
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
            Route::get('/appointment', [DashboardController::class, 'appointment'])->name('appointment.index');
            Route::get('/search', [\App\Http\Controllers\Dashboard\SearchController::class, 'index'])->name('search.index');

            // Editor Upload Image
            Route::post('upload-image', [UploadImageController::class, 'index'])->name('upload.image');

            // Administrators
            Route::resource('admins', AdminController::class)->except(['show']);
            Route::get('admins/show/{id}', [AdminController::class, 'show'])->name('admins.show');
            Route::post('admins/getData', [AdminController::class, 'get_data'])->name('admins.getData');
            Route::get('admins/change-password/{id}', [AdminController::class, 'change_password_form'])->name('admins.change-password');
            Route::post('admins/save-password/{id}', [AdminController::class, 'save_password'])->name('admins.save_password');

            //// clients

            Route::get('profile', [AdminProfileController::class, 'index'])->name('my-profile');
            Route::post('update-profile', [AdminProfileController::class, 'update_profile'])->name('update_profile');
            Route::get('change-password', [AdminProfileController::class, 'change_password_form'])->name('change-my-password');
            Route::post('save-password', [AdminProfileController::class, 'save_password'])->name('save_my_password');

            // Roles
            Route::resource('roles', RoleController::class)->except(['show']);
            Route::post('roles/getData', [RoleController::class, 'get_data'])->name('roles.getData');
            //users
            Route::resource('users', UserController::class)->except(['show']);
            Route::get('users/show/{id}', [UserController::class, 'show'])->name('users.show');
            Route::post('users/getData', [UserController::class, 'get_data'])->name('users.getData');
            Route::post('users/active_toogler/{id}', [UserController::class, 'active_toogler'])->name('users.active_toogler');

            Route::resource('vendors', VendorController::class)->except(['show']);
            Route::get('vendors/show/{id}', [VendorController::class, 'show'])->name('vendors.show');
            Route::post('vendors/getData', [VendorController::class, 'get_data'])->name('vendors.getData');
            Route::post('vendors/active_toogler/{id}', [VendorController::class, 'active_toogler'])->name('vendors.active_toogler');
            //partners
            Route::resource('partners', PartnerController::class)->except(['show']);
            Route::get('partners/show/{id}', [PartnerController::class, 'show'])->name('partners.show');
            Route::post('partners/active_toogler/{id}', [PartnerController::class, 'active_toogler'])->name('partners.active_toogler');
            Route::post('partners/getData', [PartnerController::class, 'get_data'])->name('partners.getData');
            Route::get('partners/change-password/{id}', [PartnerController::class, 'change_password_form'])->name('partners.change-password');
            Route::post('partners/save-password/{id}', [PartnerController::class, 'save_password'])->name('partners.save_password');




            //videos
            Route::resource('videos', VideoController::class)->except(['show']);
            Route::post('videos/getData', [VideoController::class, 'get_data'])->name('videos.getData');
            Route::post('videos/active_toogler/{id}', [VideoController::class, 'active_toogler'])->name('videos.active_toogler');
            Route::get('videos/show/{id}', [VideoController::class, 'show'])->name('videos.show');


            //auctions
            Route::resource('auctions', AuctionController::class)->except(['show']);
            Route::post('auctions/getData', [AuctionController::class, 'get_data'])->name('auctions.getData');
            Route::post('auctions/active_toogler/{id}', [AuctionController::class, 'active_toogler'])->name('auctions.active_toogler');
            Route::get('auctions/show/{id}', [AuctionController::class, 'show'])->name('auctions.show');


            Route::resource('orders', OrderController::class)->except(['show', 'create', 'store']);
            Route::post('orders/getData', [OrderController::class, 'get_data'])->name('orders.getData');
            Route::get('orders/show/{id}', [OrderController::class, 'show'])->name('orders.show');
            Route::post('orders/{order}/piece-services', [OrderPieceServiceController::class, 'store'])->name('order-piece-services.store');
            Route::put('piece-services/{id}', [OrderPieceServiceController::class, 'update'])->name('order-piece-services.update');
            Route::delete('piece-services/{id}', [OrderPieceServiceController::class, 'destroy'])->name('order-piece-services.destroy');


            Route::resource('products', ProductController::class)->except(['show']);
            Route::get('products/create/{id}', [ProductController::class, 'create'])->name('products.create');
            Route::get('products/{id}', [ProductController::class, 'index'])->name('products.index');

            Route::post('products/getData/{id}', [ProductController::class, 'get_data'])->name('products.getData');
            Route::post('products/active_toogler/{id}', [ProductController::class, 'active_toogler'])->name('products.active_toogler');
            Route::get('products/show/{id}', [ProductController::class, 'show'])->name('products.show');
            Route::get('products/comments/{id}', [ProductController::class, 'comments'])->name('products.comments');
            Route::post('products/comments/getData/{id}', [ProductController::class, 'comments_get_data'])->name('products.comments.getData');
            Route::delete('products/comments/delete/{id}', [ProductController::class, 'comments_delete'])->name('products.comments.delete');






            // Countries & Cities
            Route::resource('/cities', CityController::class)->except(['show']);
            Route::post('cities/active_toogler/{id}', [CityController::class, 'active_toogler'])->name('cities.active_toogler');
            Route::post('cities/getData', [CityController::class, 'get_data'])->name('cities.getData');

            // Gifts

            Route::resource('/gifts', GiftController::class)->except(['show']);
            Route::post('gifts/active_toogler/{id}', [GiftController::class, 'active_toogler'])->name('gifts.active_toogler');
            Route::post('gifts/getData', [GiftController::class, 'get_data'])->name('gifts.getData');
            // Gifts
            // packages

            Route::resource('/packages', PackageController::class)->except(['show']);
            Route::get('packages/show/{id}', [PackageController::class, 'show'])->name('packages.show');
            Route::post('packages/active_toogler/{id}', [PackageController::class, 'active_toogler'])->name('packages.active_toogler');
            Route::post('packages/getData', [PackageController::class, 'get_data'])->name('packages.getData');
            // packages
            // user-subscriptions
            Route::resource('/user-subscriptions', UserSubscriptionController::class)->except(['create', 'store', 'edit', 'update']);
            Route::post('user-subscriptions/getData', [UserSubscriptionController::class, 'get_data'])->name('user-subscriptions.getData');
            Route::post('user-subscriptions/{id}/approve', [UserSubscriptionController::class, 'approve'])->name('user-subscriptions.approve');
            Route::post('user-subscriptions/{id}/reject', [UserSubscriptionController::class, 'reject'])->name('user-subscriptions.reject');
            // user-subscriptions
            // seller-submissions
            Route::resource('/seller-submissions', SellerSubmissionController::class)->only(['index', 'show']);
            Route::post('seller-submissions/getData', [SellerSubmissionController::class, 'get_data'])->name('seller-submissions.getData');
            Route::post('seller-submissions/{id}/approve', [SellerSubmissionController::class, 'approve'])->name('seller-submissions.approve');
            Route::post('seller-submissions/{id}/reject', [SellerSubmissionController::class, 'reject'])->name('seller-submissions.reject');
            Route::post('seller-submissions/{id}/request-edit', [SellerSubmissionController::class, 'request_edit'])->name('seller-submissions.request-edit');
            // seller-submissions
            // categories
            Route::resource('/categories', CategoryController::class)->except(['show']);
            Route::post('categories/active_toogler/{id}', [CategoryController::class, 'active_toogler'])->name('categories.active_toogler');
            Route::post('categories/getData', [CategoryController::class, 'get_data'])->name('categories.getData');
            // categories
            // settings
            Route::get('settings/edit', [SettingController::class, 'edit'])->name('settings.edit');
            Route::post('settings/update', [SettingController::class, 'update'])->name('settings.update');
            // settings
            // sliders
            Route::resource('/sliders', SliderController::class)->except(['show']);
            Route::post('sliders/active_toogler/{id}', [SliderController::class, 'active_toogler'])->name('sliders.active_toogler');
            Route::post('sliders/getData', [SliderController::class, 'get_data'])->name('sliders.getData');
            // sliders
            // colors
            Route::resource('/colors', ColorController::class)->except(['show']);
            Route::post('colors/active_toogler/{id}', [ColorController::class, 'active_toogler'])->name('colors.active_toogler');
            Route::post('colors/getData', [ColorController::class, 'get_data'])->name('colors.getData');
            // colors
            // ages
            Route::resource('/ages', AgeController::class)->except(['show']);
            Route::post('ages/active_toogler/{id}', [AgeController::class, 'active_toogler'])->name('ages.active_toogler');
            Route::post('ages/getData', [AgeController::class, 'get_data'])->name('ages.getData');
            // ages
            // item-services
            Route::resource('/item-services', ItemServiceController::class)->except(['show']);
            Route::post('item-services/active_toogler/{id}', [ItemServiceController::class, 'active_toogler'])->name('item-services.active_toogler');
            Route::post('item-services/getData', [ItemServiceController::class, 'get_data'])->name('item-services.getData');
            // item-services
            // animal-pens
            Route::resource('/animal-pens', AnimalPenController::class)->except(['show']);
            Route::post('animal-pens/active_toogler/{id}', [AnimalPenController::class, 'active_toogler'])->name('animal-pens.active_toogler');
            Route::post('animal-pens/getData', [AnimalPenController::class, 'get_data'])->name('animal-pens.getData');
            // animal-pens




            // page

            Route::resource('/pages', PageController::class)->except(['show']);
            // page

            // Sounds

            Route::resource('/sounds', SoundController::class)->except(['show']);
            Route::post('sounds/active_toogler/{id}', [SoundController::class, 'active_toogler'])->name('sounds.active_toogler');
            Route::post('sounds/getData', [SoundController::class, 'get_data'])->name('sounds.getData');
            // Gifts

            // reports
            Route::resource('/reports', ReportController::class)->except(['show']);
            Route::post('reports/active_toogler/{id}', [ReportController::class, 'active_toogler'])->name('reports.active_toogler');
            Route::post('reports/getData', [ReportController::class, 'get_data'])->name('reports.getData');
            // reports
            // reports
            Route::resource('/report-users', ReportUserController::class)->except(['show']);
            Route::post('report-users/active_toogler/{id}', [ReportUserController::class, 'active_toogler'])->name('report.users.active_toogler');
            Route::post('report-users/getData', [ReportUserController::class, 'get_data'])->name('report.users.getData');
            // reports
            //notifications
            Route::resource('/notifications', NotificationsController::class)->except(['show']);
            Route::post('notifications/active_toogler/{id}', [NotificationsController::class, 'active_toogler'])->name('notifications.active_toogler');
            Route::post('notifications/getData', [NotificationsController::class, 'get_data'])->name('notifications.getData');
            Route::get('notifications/view/{id}', [NotificationsController::class, 'view'])->name('notifications.view');
            //notifications

            // contact messages
            Route::resource('/contact-messages', ContactMessageController::class)->only(['index', 'show', 'destroy']);
            Route::post('contact-messages/getData', [ContactMessageController::class, 'get_data'])->name('contact-messages.getData');
            // contact messages

            // partner finance
            Route::get('partner-finance/invoices', [PartnerFinanceController::class, 'invoices'])->name('partner-finance.invoices');
            Route::get('partner-finance/wallet', [PartnerFinanceController::class, 'wallet'])->name('partner-finance.wallet');
            // partner finance

        });

        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('auth.loginform');
        Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
        Route::get('/logout', [LoginController::class, 'logout'])->name('auth.logout');
    });
});
?>
