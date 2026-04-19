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

Route::group(['prefix' => 'user', 'namespace' => 'User'], function () {
    // Home Page

    Route::group(['namespace' => 'Auth'], function () {
        // Register
        Route::post('register', 'RegisterController');
        // Login
        Route::post('forgot-password', 'RegisterController@forgetPassword');
        Route::post('reset-password', 'ResetPasswordController');

        Route::post('login', 'LoginController');
        Route::group(['namespace' => 'Social'], function () {
        Route::post('social-login', 'LoginController');
        Route::post('social-register', 'RegisterController');


        });
    });

    Route::get('partners', 'PartnerController@index');




//    Route::group(['middleware' => ['auth:api']], function() {
    Route::group(['namespace' => 'Profile'], function(){
        Route::get('video', 'UserProfileController@OtherVideo');
        Route::get('only-me-video', 'UserProfileController@myVideo');
        Route::get('profile', 'UserProfileController');
        Route::get('balance', 'UserProfileController@balance');
        Route::post('profile_completed', 'ProfileCompleteController');

        Route::get('my-cart', 'UserProfileController@MyCart');
        Route::post('cart/add-address', 'UserProfileController@addAddress');

        Route::get('user-profile/{user_name}', 'UserProfileController@otherUserprofile');
        Route::post('logout', 'UserProfileController@logout');
        Route::post('delete-account', 'UserProfileController@deleteAccount');
        Route::post('update-name', 'UpdateProfileController@updateName');
        Route::post('update-profile', 'UpdateProfileController@updateProfile');

        Route::post('update-username', 'UpdateProfileController@updateUserName');
        Route::post('update-bio', 'UpdateProfileController@updateBio');
        Route::post('update-image', 'UpdateProfileController@updateImage');
        Route::post('update-files', 'UpdateProfileController@updateFiles');
        Route::post('update-password', 'ChangePasswordController');
        Route::post('profile-view/{id}', 'ProfileActionController@profileView');
        Route::get('profile-view-list', 'ProfileActionController@profileViewList');
        Route::post('block/{id}', 'ProfileActionController@block');
        Route::post('is-block/{id}', 'ProfileActionController@isBlock');
        Route::get('block-list', 'ProfileActionController@blockList');
        Route::post('update-fcm', 'UserProfileController@updateFcm');
        Route::post('update-lang', 'UserProfileController@updateLang');
        Route::post('subscription', 'UserCoinController@subscription');
        Route::get('subscription-list', 'UserCoinController@subscriptionList');

        // Auction Subscriptions - Public endpoint for plans
        Route::get('auction-subscription-plans', 'AuctionSubscriptionController@getPlans');

        Route::get('notifications', 'UserProfileController@notifications');


    });
        Route::post('seller-submissions', 'SellerSubmissionController@store');

//    });

});

    Route::group(['prefix' => 'video','namespace' => 'Video'], function(){

        Route::group(['prefix' => 'auctions'], function() {
            Route::get('list/{id}', 'AuctionVideoController@list');
            Route::get('replay-list/{id}', 'AuctionVideoController@listReplay');
        });

    });


            Route::group(['prefix' => 'user','namespace' => 'User'], function(){

                Route::group(['prefix' => 'follow'], function(){
                    Route::get('followers-list/{id}', 'FollowUserController@followersList');
                });
                Route::group(['prefix' => 'friend','namespace' => 'Friend'], function(){
                    Route::get('search', 'FriendController@search');
                    Route::post('suggest', 'FriendController@suggest');
                    Route::get('video', 'FriendController@video');
                    Route::get('video/view-count', 'FriendController@viewCount');

                });
                Route::post('report-add/{id}', 'UserReportController@add');



            });





Route::group(['middleware' => ['auth:api']], function() {

        Route::group(['prefix' => 'user','namespace' => 'User'], function(){

            Route::group(['namespace' => 'Profile'], function(){
                // Auction Subscriptions - Protected routes
                Route::get('auction-subscription-status', 'AuctionSubscriptionController@getStatus');
                Route::get('auction-subscription-history', 'AuctionSubscriptionController@getHistory');
                Route::post('auction-subscription', 'AuctionSubscriptionController@subscribe');
            });

            Route::get('seller-submissions/my-list', 'SellerSubmissionController@myList');
            Route::get('seller-submissions/{id}', 'SellerSubmissionController@show');

            Route::group(['prefix' => 'follow'], function(){
                Route::get('list', 'FollowUserController@list');
                Route::get('followers-list', 'FavoritesVideoController@followersList');

                Route::post('add/{id}', 'FollowUserController@add');
                Route::post('unfollow/{id}', 'FollowUserController@unfollow');
            });

            Route::group(['prefix' => 'auction','namespace' => 'Invoice'], function(){
                Route::get('invoice', 'AuctionController@list');
                Route::get('invoice/{id}', 'AuctionController@Iteam');

                Route::get('user-invoice', 'UserAuctionController@list');
                Route::get('user-invoice/{id}', 'UserAuctionController@Iteam');
                Route::post('upload-win-video/{id}', 'UserAuctionController@uploadWinVideo');
                Route::post('upload-payment-proof', 'UserAuctionController@uploadPaymentProof');
            });

            Route::group(['prefix' => 'friend','namespace' => 'Friend'], function(){
                Route::post('add/{id}', 'FriendController@add');
                Route::post('unfriend/{id}', 'FriendController@unfriend');
                Route::get('list', 'FriendController@List');
                Route::get('request-list', 'FriendController@requestList');
                Route::get('my-send-request-list', 'FriendController@myRequestList');
                Route::post('accept-friend-request/{id}', 'FriendController@acceptFriendRequest');

            });


        });


        Route::group(['prefix' => 'home','namespace' => 'Home'], function () {
            Route::get('video-follow/list', 'HomeVideoController@followVideo');
        });


        Route::group(['prefix' => 'video','namespace' => 'Video'], function(){
            Route::post('add', 'VideoController');
            Route::post('add-view/{id}', 'VideoController@addView');
            Route::post('delete/{id}', 'VideoController@delete');
            Route::post('add-share/{id}', 'VideoController@addShare');


            Route::get('video-search-name/{name}', 'VideoController@searchName');
            Route::get('search-user-name-video/{name}', 'VideoController@searchUserName');


            Route::group(['prefix' => 'like'], function(){
                Route::post('{id}', 'LikeVideoController');
                Route::post('add/{id}', 'LikeVideoController@add');
                Route::post('dislike/{id}', 'LikeVideoController@dislike');
            });
            Route::group(['prefix' => 'auctions'], function(){
                Route::post('add', 'AuctionVideoController@add');
                Route::post('add-replay', 'AuctionVideoController@addReplay');
                Route::group(['prefix' => 'like'], function(){
                    Route::post('add/{id}', 'AuctionVideoController@addLike');
                    Route::post('dislike/{id}', 'AuctionVideoController@dislike');
                });


            });
            Route::group(['prefix' => 'comment'], function(){
                Route::post('add', 'CommentVideoController@add');
                Route::get('list/{id}', 'CommentVideoController@list');

            });



            Route::group(['prefix' => 'favorites'], function(){
                Route::get('list', 'FavoritesVideoController');
                Route::post('add/{id}', 'FavoritesVideoController@add');
                Route::post('unfavorited/{id}', 'FavoritesVideoController@unfavorited');
            });
            Route::group(['prefix' => 'report'], function(){
                Route::post('add/{id}', 'VideoReportController@add');
            });
        });


        Route::group(['prefix' => 'live','namespace' => 'Live'], function(){
            Route::post('add', 'LiveVideoController@add');
            Route::post('start/{id}', 'LiveVideoController@start');
            Route::get('last-auction/{id}', 'LiveVideoController@lastAuction');
            Route::post('end/{id}', 'LiveVideoController@end');
            Route::post('update/{id}', 'LiveVideoController@update');
            Route::post('delete/{id}', 'LiveVideoController@delete');
            Route::post('update-time/{id}', 'LiveVideoController@updateTime');
            Route::post('add-like/{id}', 'LiveVideoController@addLike');
            Route::get('like-count/{id}', 'LiveVideoController@LikeCount');
            Route::get('list-user', 'LiveVideoController@ListUser');
            Route::get('my-list', 'LiveVideoController@myList');
            Route::get('my-video-view/{id}', 'LiveVideoController@myVideoViewList');

            Route::get('video/{id}', 'LiveVideoController@SingleVideo');
            Route::post('send-gift', 'LiveVideoController@sendGift');
            Route::post('add-view/{id}', 'LiveVideoController@addView');
            Route::post('leave-view/{id}', 'LiveVideoController@leaveView');
            Route::get('refresh-token/{id}', 'LiveVideoController@refreshAgoraToken');
            Route::group(['prefix' => 'items'], function(){
                Route::post('add/{id}', 'LiveVideoItemController@add');
                Route::post('update/{id}', 'LiveVideoItemController@update');
                Route::post('delete/{id}', 'LiveVideoItemController@delete');
                Route::post('start/{id}', 'LiveVideoItemController@start');
                Route::post('end/{id}', 'LiveVideoItemController@end');
                Route::get('last-auction/{id}', 'LiveVideoItemController@lastAuction');
                Route::post('auction-award/{id}', 'LiveVideoItemController@auctionAward');

            });
        });
        Route::group(['prefix' => 'story','namespace' => 'Story'], function(){
            Route::post('add', 'StoryController');
            Route::get('my-list', 'StoryController@myList');
            Route::get('my-active', 'StoryController@myActive');
            Route::post('add-view/{id}', 'StoryController@addView');
            Route::get('list', 'StoryController@showList');

        });

     });

        Route::group(['prefix' => 'home','namespace' => 'Home'], function () {
            Route::get('video/list', 'HomeVideoController');
            Route::get('video/{id}', 'HomeVideoController@show');
            Route::get('user-videos/{id}', 'HomeVideoController@userVideos');

        });
        Route::group(['prefix' => 'video','namespace' => 'Video'], function(){
            Route::post('add-view/{id}', 'VideoController@addView');
            Route::post('add-share/{id}', 'VideoController@addShare');

        });


