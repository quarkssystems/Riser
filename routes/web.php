<?php

use App\Models\Post;
use App\Models\PaymentPayout;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\PostsController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\TalukaController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CreatorController;
use App\Http\Controllers\Webhook\BunnyController;
use App\Http\Controllers\Admin\CmsPagesController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\LanguagesController;
use App\Http\Controllers\Admin\CategoriesController;
use App\Http\Controllers\Webhook\RazorPayController;
use App\Http\Controllers\Admin\CallBookingController;
use App\Http\Controllers\Admin\CallPackageController;
use App\Http\Controllers\Admin\MasterClassController;
use App\Http\Controllers\Admin\BannerCategoryController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\Admin\PaymentTransactionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Jobs\SendPostAwsToBunnyJob;

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

//Webhook Routes
Route::post('media-status', [BunnyController::class, 'videoStatus']);
Route::post('razorpay-webhook', [RazorPayController::class, 'transactionStatus']);

//Mobile Deep Link Routes for user profile
Route::get('user/{key}', function($key) {
    $url = config('constant.playstore_url').'&user='.$key;
    return  Redirect::to($url);
});

Route::get('user/video/{key}', function($key) {
    $url = config('constant.playstore_url').'&video='.$key;
    return  Redirect::to($url);
});

Route::get('referral/{key}', function($key) {
    $url = config('constant.playstore_url').'&referral='.$key;
    return  Redirect::to($url);
});

//Mobile Deep Link Routes for share master class
Route::get('share-master-class/{masterClass}/{creator}/{promoter?}', function($masterClass, $creator, $promoter = null) {
    $promoterUrl = $promoter ? '&promoter='.$promoter : '';
    $url = config('constant.playstore_url').'&masterClass='.$masterClass.'&creator='.$creator.$promoterUrl;
    return  Redirect::to($url);
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', function () {
    return redirect('admin/login');
});

Route::group(['prefix' => 'admin'], function () {
    Auth::routes(['register'=>false]);
});

Route::middleware(['auth'])->group(function () {
    //Common Route
    Route::post('states-list', [CommonController::class, 'getStatesList']);
    Route::post('districts-list', [CommonController::class, 'getDistrictsList']);
    Route::post('talukas-list', [CommonController::class, 'getTalukasList']);
    Route::post('users-list', [CommonController::class, 'getUsersList']);
});

Route::prefix('admin')->middleware(['auth','admin','optimizeImages'])->group(function () {
    
    Route::get('/', function(){
        // $adminIncome = PaymentPayout::where('role',config('constant.roles.admin'))
        //     ->sum('payout_amount');
        // return view('dashboard', compact('adminIncome'));
        return redirect('admin/dashboard');
    });
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::put('/profile/{user}', [UserController::class, 'profileUpdate'])->name('profile.update');

    Route::resource('user', UserController::class)->middleware('permission:create-user|read-user|update-user|delete-user');
    //Route::get('/user-list', [UserController::class, 'userList'])->name('user.list');

    //Admin User Route
    Route::resource('admins', AdminController::class)->middleware('permission:create-user|read-user|update-user|delete-user');
    Route::post('admins/change-status/{admin}', [AdminController::class, 'changeStatus'])->name('admins.change-status');

    //Creator User Route
    Route::get('creators/invited', [CreatorController::class, 'indexInvited'])->name('creators.invited');
    Route::get('creators/invited/show/{creator}', [CreatorController::class, 'showInvited'])->name('creators.invited.show');
    Route::delete('creators/invited/delete/{creator}', [CreatorController::class, 'destroyInvited'])->name('creators.invited.destroy');
    Route::post('creators/invited/change-status/{creator}', [CreatorController::class, 'changeInvitationStatus'])->name('creators.change-invitation-status');
    Route::get('creators/invited/posts/{creator}', [CreatorController::class, 'getInvitedPosts'])->name('creators.invited.posts');

    Route::resource('creators', CreatorController::class)->middleware('permission:create-user|read-user|update-user|delete-user');
    Route::post('creators/change-status/{creator}', [CreatorController::class, 'changeStatus'])->name('creators.change-status');
    Route::get('creators/posts/{creator}', [CreatorController::class, 'getPosts'])->name('creators.posts');    

    //Agent User Route
    Route::get('agents/invited', [AgentController::class, 'indexInvited'])->name('agents.invited');
    Route::get('agents/invited/show/{agent}', [AgentController::class, 'showInvited'])->name('agents.invited.show');
    Route::delete('agents/invited/delete/{agent}', [AgentController::class, 'destroyInvited'])->name('agents.invited.destroy');
    Route::post('agents/invited/change-status/{agent}', [AgentController::class, 'changeInvitationStatus'])->name('agents.change-invitation-status');
    Route::resource('agents', AgentController::class)->middleware('permission:create-user|read-user|update-user|delete-user');
    Route::post('agents/change-status/{agent}', [AgentController::class, 'changeStatus'])->name('agents.change-status');

    //User User Route
    Route::resource('users', AdminUserController::class)->middleware('permission:create-user|read-user|update-user|delete-user');
    Route::post('users/change-status/{user}', [AdminUserController::class, 'changeStatus'])->name('users.change-status');


    //Master Languages Route
    Route::resource('languages', LanguagesController::class);
    Route::post('languages/change-status/{language}', [LanguagesController::class, 'changeStatus'])->name('languages.change-status');

    //Master Categories Route
    Route::resource('categories', CategoriesController::class);
    Route::post('categories/change-status/{category}', [CategoriesController::class, 'changeStatus'])->name('categories.change-status');

    //CMS Pages Route
    Route::resource('cms-pages', CmsPagesController::class);
    Route::post('cms-pages/change-status/{cmsPage}', [CmsPagesController::class, 'changeStatus'])->name('cms-pages.change-status');

    //Banner Category Route
    Route::resource('banner-categories', BannerCategoryController::class);
    Route::post('banner-categories/change-status/{bannerCategory}', [BannerCategoryController::class, 'changeStatus'])->name('banner-categories.change-status');

    //Banners Route
    Route::resource('banners', BannerController::class);
    Route::post('banners/change-status/{banner}', [BannerController::class, 'changeStatus'])->name('banners.change-status');

    //Feedbacks Route
    Route::get('feedbacks/index', [FeedbackController::class, 'index'])->name('feedbacks.index');
    Route::get('feedbacks/show/{feedback}', [FeedbackController::class, 'show'])->name('feedbacks.show');

    //Country Master Route
    Route::resource('countries', CountryController::class);
    Route::post('countries/change-status/{country}', [CountryController::class, 'changeStatus'])->name('countries.change-status');

    //State Master Route
    Route::resource('states', StateController::class);
    Route::post('states/change-status/{state}', [StateController::class, 'changeStatus'])->name('states.change-status');

    //District Master Route
    Route::resource('districts', DistrictController::class);
    Route::post('districts/change-status/{district}', [DistrictController::class, 'changeStatus'])->name('districts.change-status');

    //Taluka Master Route
    Route::resource('talukas', TalukaController::class);
    Route::post('talukas/change-status/{taluka}', [TalukaController::class, 'changeStatus'])->name('talukas.change-status');

    //Posts Route
    Route::resource('posts', PostsController::class);
    Route::post('posts/change-status/{post}', [PostsController::class, 'changeStatus'])->name('posts.change-status');
    Route::get('posts/comments/{post}', [PostsController::class, 'getPostComments'])->name('posts.comments');
    Route::get('posts/likes/{post}', [PostsController::class, 'getPostLikes'])->name('posts.likes');
    Route::get('posts/reports/{post}', [PostsController::class, 'getPostReports'])->name('posts.reports');

    //Master Classes Route
    Route::resource('master-classes', MasterClassController::class);
    Route::post('master-classes/change-status/{masterClass}', [MasterClassController::class, 'changeStatus'])->name('posts.change-status');
    Route::get('master-classes/users/{masterClass}', [MasterClassController::class, 'getMasterClassUsers'])->name('master-classes.users');
    Route::get('master-classes/promoters/{masterClass}', [MasterClassController::class, 'getMasterClassPromoters'])->name('master-classes.promoters');
    Route::get('master-classes/affilitors/{masterClass}', [MasterClassController::class, 'getMasterClassAffilitors'])->name('master-classes.affilitors');
    Route::get('master-classes/transactions/{masterClass}', [MasterClassController::class, 'getMasterClassTransactions'])->name('master-classes.transactions');
    
    //Call Booking Route
    Route::resource('call-bookings', CallBookingController::class);
    Route::post('call-bookings/change-status/{callBooking}', [CallBookingController::class, 'changeStatus'])->name('call-bookings.change-status');
    Route::get('call-bookings/transactions/{callBooking}', [CallBookingController::class, 'getCallBookingTransactions'])->name('call-bookings.transactions');
    
    //Call Package Route
    Route::resource('call-packages', CallPackageController::class);
    Route::post('call-packages/change-status/{callPackage}', [CallPackageController::class, 'changeStatus'])->name('call-packages.change-status');
    
    //Payment Transactions Route
    Route::resource('payment-transactions', PaymentTransactionController::class);
    
    //Admin Transactions Route
    Route::resource('admin-transactions', AdminTransactionController::class);

    //manually dispatch job to upload video on bunny
    Route::get('man-job-upload/{id?}', function($id = null) {
        if($id) {
            $post = App\Models\Post::find($id);
            if($post && $post->media_type == 'video'){
                dispatch(new \App\Jobs\SendPostAwsToBunnyJob($post));
                return 'success single';
            }
        } else {
            App\Models\Post::whereNotNull('media_url')->status('processing')
            ->chunkById(100, function($posts)  {
                foreach($posts as $post) {
                    dispatch(new \App\Jobs\SendPostAwsToBunnyJob($post));          
                }
            });
            return 'success multiple';
        }
        return 'fail';
    });

    //check if posts exist in AWS
    Route::get('aws-post-exist', function() {
        App\Models\Post::whereNotNull('media_url')->status('processing')
            ->chunkById(10, function($posts)  {
                foreach($posts as $post) {
                    if(!fileExists($post->media_url)) {
                        $post->delete();
                    }
                }
            });
        return 'success multiple';
    });
});
