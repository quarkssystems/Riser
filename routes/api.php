<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PaytmController;
use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\RazorPayController;
use App\Http\Controllers\Api\CallBookingController;
use App\Http\Controllers\Api\MasterClassController;
use App\Http\Controllers\Api\MultiMasterClassController;
use App\Http\Controllers\Api\UserBankAccountController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('register-user', [AuthController::class, 'registerUser'])->name('register-user');
Route::post('register-via-phone', [AuthController::class, 'registerViaPhone'])->name('register-via-phone');
Route::post('social-login', [AuthController::class, 'socialLogin'])->name('social-login');
Route::post('login', [AuthController::class, 'login'])->name('login');

// Get APIs with versions and without login as guest
Route::group([
    'middleware' => ['guest', 'localization'],
    'prefix' => 'v1',
], function ($router) {
    Route::get('cms-page/{slug}', [CommonController::class, 'getCmsPage']);
    Route::get('categories-list', [CommonController::class, 'getCategoriesList']);
    Route::get('banners/{slug?}', [CommonController::class, 'getBanners']);
    Route::get('popular-creators', [UserController::class, 'getPopularCreators']);
    Route::get('post-list', [PostController::class, 'getPostList']);
    Route::get('user-post-list', [PostController::class, 'getUserPostList']);
    Route::post('update-views', [PostController::class, 'updateViews']);
    Route::post('search-profile-or-video', [PostController::class, 'searchProfileOrVideo']);
    Route::get('master-class-list-by-category', [MasterClassController::class, 'getMasterClassListByCategory']);

    Route::post('phone-exist', [UserController::class, 'checkPhoneExist']);
    Route::post('update-password', [UserController::class, 'updatePassword']);
});

Route::group([
    'middleware' => ['auth:sanctum', 'localization', 'optimizeImages'],
    'prefix' => 'v1',
], function ($router) {
    Route::post('logout', [AuthController::class, 'logout']);

    //For Firebase token and devices
    Device::routes('App\Http\Controllers\Api');

    //Common APIs
    Route::get('countries-list', [CommonController::class, 'getCountriesList']);
    Route::post('states-list', [CommonController::class, 'getStatesList']);
    Route::post('districts-list', [CommonController::class, 'getDistrictsList']);
    Route::post('talukas-list', [CommonController::class, 'getTalukasList']);
    Route::post('search-location', [CommonController::class, 'searchLocation']);
    Route::get('languages-list', [CommonController::class, 'getLanguagesList']);
    Route::get('hashtags-list', [CommonController::class, 'getHashtagsList']);
    Route::post('send-feedback', [CommonController::class, 'sendFeedback']);

    //User APIs
    Route::post('save-user-location', [UserController::class, 'saveUserLocations']);
    Route::get('user-profile', [UserController::class, 'getUserProfile']);
    Route::post('edit-user-profile', [UserController::class, 'editUserProfile']);
    Route::post('edit-user-email', [UserController::class, 'editUserEmail']);
    Route::post('follow-unfollow', [UserController::class, 'followUnfollow']);
    Route::get('user-dashboard', [UserController::class, 'getUserDashboard']);
    Route::get('creator-dashboard', [UserController::class, 'getCreatorDashboard']);
    Route::get('my-purchased-master-class', [UserController::class, 'myPurchasedMasterClass']);
    Route::get('view-notifications', [UserController::class, 'viewNotifications']);
    Route::post('add-bank-account', [UserBankAccountController::class, 'addBankAccount']);
    Route::get('get-bank-accounts', [UserBankAccountController::class, 'getBankAccounts']);
    Route::post('edit-bank-account', [UserBankAccountController::class, 'editBankAccount']);
    Route::delete('delete-bank-account', [UserBankAccountController::class, 'deleteBankAccount']);

    //follower-following list
    Route::get('user-followers', [UserController::class, 'getUserFollowers']);
    Route::get('user-following', [UserController::class, 'getUserFollowing']);
    Route::get('user-subtree', [UserController::class, 'getUserSubTree']);

    Route::post('block-user', [UserController::class, 'blockUser']);

    //Post/Video APIs
    Route::post('create-post', [PostController::class, 'createPost']);
    Route::get('post-detail', [PostController::class, 'getPostDetail']);
    Route::get('post-detail-likes', [PostController::class, 'getPostDetailLikes']);
    Route::get('post-detail-comments', [PostController::class, 'getPostDetailComments']);
    Route::post('add-post-comments', [PostController::class, 'addPostComments']);
    Route::post('like-dislike', [PostController::class, 'likeDislike']);
    Route::post('report-post', [PostController::class, 'reportPost']);
    Route::post('report-profile', [PostController::class, 'reportProfile']);
    Route::delete('delete-post', [PostController::class, 'deletePost']);

    Route::post('block-post', [PostController::class, 'blockPost']);

    //Master Class APIs
    Route::post('create-master-class', [MasterClassController::class, 'createMasterClass']);
    Route::get('master-class-list', [MasterClassController::class, 'getMasterClassList']);
    Route::post('edit-master-class', [MasterClassController::class, 'editMasterClass']);
    Route::delete('delete-master-class', [MasterClassController::class, 'deleteMasterClass']);
    Route::post('enroll-master-class', [MasterClassController::class, 'enrollMasterClass']);
    Route::post('promote-master-class', [MasterClassController::class, 'promoteMasterClass']);
    Route::get('master-class-detail', [MasterClassController::class, 'getMasterClassDetail']);
    Route::get('get-master-class-bookings', [MasterClassController::class, 'getMasterClassBookings']);
    Route::get('get-affiliate', [MasterClassController::class, 'getAffiliate']);
    Route::post('update-meeting-link', [MasterClassController::class, 'updateMeetingLink']);
    Route::post('update-mastar-class-start', [MasterClassController::class, 'updateMasterClassStart']);

    //Call Booking APIs
    Route::get('call-booking-packages', [CallBookingController::class, 'getCallBookingPackages']);
    Route::post('call-booking-request', [CallBookingController::class, 'callBookingRequest']);
    Route::get('call-booking-list', [CallBookingController::class, 'getCallBookingList']);
    Route::post('accept-reject-call', [CallBookingController::class, 'acceptRejectCall']);
    Route::post('book-call-after-payment', [CallBookingController::class, 'bookCallAfterPayment']);
    Route::post('join-call', [CallBookingController::class, 'joinCall']);

    //Paytm APIs
    Route::post('create-checksum', [PaytmController::class, 'createChecksum']);
    Route::post('transaction-status', [PaytmController::class, 'transactionStatus']);
    Route::post('razorpay-create-order', [RazorPayController::class, 'razorPayCreateOrder']);

    //Wallet APIs
    Route::get('get-wallet-balance', [WalletController::class, 'getWalletBalance']);
    Route::post('add-balance-to-wallet', [WalletController::class, 'addBalanceToWallet']);
    Route::post('withdraw-from-wallet', [WalletController::class, 'withdrawFromWallet']);
    Route::post('send-gift-to-creator', [WalletController::class, 'sendGiftToCreator']);
    Route::get('gift-transactions', [WalletController::class, 'giftTransactions']);
    Route::get('my-earning', [WalletController::class, 'myEarning']);
    Route::get('my-team-earning', [WalletController::class, 'myTeamEarning']);

    //Chat APIs
    Route::post('send-message', [ChatController::class, 'sendMessage'])->name('send-message');
    Route::get('get-recent-chat', [ChatController::class, 'getRecentChat'])->name('get-recent-chat');
    Route::get('get-recent-chat-list', [ChatController::class, 'getRecentChatList'])->name('get-recent-chat-list');
    Route::post('new-message-to', [ChatController::class, 'newMessageTo'])->name('new-message-to');
    Route::post('get-chat-media', [ChatController::class, 'getChatMedia'])->name('get-chat-media');
    Route::get('get-chat-documents', [ChatController::class, 'getDocuments'])->name('get-chat-documents');
    Route::get('get-chat-images', [ChatController::class, 'getImages'])->name('get-chat-images');
    Route::get('get-chat-videos', [ChatController::class, 'getVideos'])->name('get-chat-videos');
    Route::Post('is-read', [ChatController::class, 'isRead'])->name('is-read');

    //Zego Cloud authentication
    Route::Post('generate-meeting-identity-token', [CommonController::class, 'generateMeetingIdentityToken'])->name('generate-meeting-identity-token');
    Route::Post('generate-meeting-privileges-token', [CommonController::class, 'generateMeetingPrivilegesToken'])->name('generate-meeting-privileges-token');
});

Route::group([
    'middleware' => ['auth:sanctum', 'localization', 'optimizeImages'],
    'prefix' => 'v2',
], function ($router) {
    //Master Class APIs
    Route::post('create-master-class', [MultiMasterClassController::class, 'createMultiMasterClass']);
});
