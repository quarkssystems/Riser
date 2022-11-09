<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Gift;
use App\Models\User;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Models\PaymentPayout;
use App\Models\MasterClassUser;
use App\Notifications\FollowUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\NotificationResource;
use Illuminate\Support\Facades\Notification;
use App\Http\Resources\NotificationCollection;

class UserController extends Controller
{
    /**
     * Save user location
     *
     * Developed By : Amish Soni
     * Developed On : 06/05/2022
     */
    /**
     * @OA\Post(
     * path="/api/v1/save-user-location",
     * operationId="save-user-location",
     * tags={"User"},
     * summary="Save user location",
     * description="Save user location",
     * security={ {"bearerAuth": {} }},
     * @OA\RequestBody(
     *  @OA\MediaType(
     *      mediaType="application/json",
     *      @OA\Schema(
     *          @OA\Property(property="latitude", type="string", example="23.0225"),
     *          @OA\Property(property="longitude", type="string", example="72.5714"),
     *          @OA\Property(property="country_id", type="integer", example=""),
     *          @OA\Property(property="state_id", type="integer", example=""),
     *          @OA\Property(property="district_id", type="integer", example=""),
     *          @OA\Property(property="taluka_id", type="integer", example=""),
     *      ),
     *  ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success"
     * ),
     *
     *
     * )
     */
    public function saveUserLocations(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'latitude' => 'required_without:country_id',
            'longitude' => 'required_with:latitude,',
            'country_id' => 'required_without:latitude',
            'state_id' => 'required_with:country_id',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $user = Auth::user();
        $data = [];

        if($request->filled('latitude')) {
            $data['latitude'] = $request->latitude;
        }

        if($request->filled('longitude')) {
            $data['longitude'] = $request->longitude;
        }

        if($request->filled('country_id')) {
            $data['country_id'] = $request->country_id;
        }

        if($request->filled('state_id')) {
            $data['state_id'] = $request->state_id;
        }

        if($request->filled('district_id')) {
            $data['district_id'] = $request->district_id;
        }

        if($request->filled('taluka_id')) {
            $data['taluka_id'] = $request->taluka_id;
        }

        $user->update($data);

        $data['user_id'] = $user->iUserId;

        $response['data'] = $data;

        return response($response);
    }

    /**
     * Get User Profile
     *
     * Developed By : Amish Soni
     * Developed On : 06/05/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/user-profile",
     *      operationId="getUserProfile",
     *      tags={"User"},
     *      summary="Get User Profile",
     *      description="Get User Profile",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="user_id",
     *          description="User Id (optional) - If not passed then loggedin user id will be used",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     */
    public function getUserProfile(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $todayDate = Carbon::now()->setTimezone('Asia/Kolkata');

        $nowDate = Carbon::now()->format('Y-m-d');
        $nowTime = Carbon::createFromFormat('Y-m-d H:i:s', (Carbon::now())->toDateTimeString())->setTimezone('Asia/Kolkata')->format('H:i');

        if($request->filled('user_id')) {
            $userData = User::where('iUserId', $request->user_id)
                ->with(['promotedMasterClass' => function($query) use ($todayDate) {
                    $query->where('master_classes.user_id', '!=', auth()->user()->iUserId)
                    ->orderBy('start_date')
                    ->status()
                    ->having(DB::raw('CONCAT(start_date," ", start_time)'), '>=', $todayDate);
                },
                // 'posts' => function($pQuery) {
                //     $pQuery->status();
                // },
                'masterClass' => function ($masterQuery) use ($nowDate, $nowTime) {
                    return $masterQuery->status()->with('categories')->whereRaw("DATE(master_classes.start_date) >= '".$nowDate."'");
                }, 'masterClassPurchased' => function ($masterQuery) use ($nowDate, $nowTime) {
                    return $masterQuery->whereRaw("DATE(master_classes.start_date) >= '" . $nowDate . "'");
                }, 'masterClassPurchased.categories','masterClass.user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id','masterClass.user.country','masterClass.user.state', 'masterClass.user.district', 'masterClass.user.taluka'])
                ->status()->first();
        } else {
            $userData = Auth::user()->load(['promotedMasterClass' => function($query) use ($todayDate) {
                return $query->orderBy('start_date')
                    ->status()
                    ->having(DB::raw('CONCAT(start_date," ", start_time)'), '>=', $todayDate);
            },
            // 'posts' => function($pQuery) {
            //     $pQuery->statusIn([config('constant.status.active_value'),config('constant.status.processing_value')]);
            // },
            'masterClass' => function ($masterQuery) use ($nowDate, $nowTime) {
                return $masterQuery->status()->with('categories')->whereRaw("DATE(master_classes.start_date) >= '" . $nowDate . "'");
            },
            'masterClassPurchased' => function ($masterQuery) use ($nowDate, $nowTime) {
                return $masterQuery->whereRaw("DATE(master_classes.start_date) >= '" . $nowDate . "'");
            }, 'masterClassPurchased.categories','masterClass.user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id','masterClass.user.country','masterClass.user.state', 'masterClass.user.district', 'masterClass.user.taluka',
                'promotedMasterClass.user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id','promotedMasterClass.user.country','promotedMasterClass.user.state', 'promotedMasterClass.user.district', 'promotedMasterClass.user.taluka',
                'masterClassPurchased.user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id','masterClassPurchased.user.country','masterClassPurchased.user.state', 'masterClassPurchased.user.district', 'masterClassPurchased.user.taluka']);
        }

        if(!$userData) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return $response;
        }

        $profileLink = (base64_encode($userData->iUserId));
        $profileLink = url('/user/'.substr(md5(microtime()), 0, 10).$profileLink);
        $referralLink = url('/referral/'.$userData->vMyCode);
        $userData->master_class_default_image = "";

        //Set default master class banner if user does not have any master class
        if($userData->masterClass->count() == 0){
            $banner = Banner::whereHas('bannerCategories', function($query){
                $query->where('slug','default-master-class-banner');
                $query->status();
            })->status()->first();
            if($banner){
                $userData->master_class_default_image = $banner->banner_image_url;
            }
        }

        $userData->wallet->refreshBalance();
        $userData->wallet_balance = $userData->balanceFloat;
        $userData->post_count = thousandsFormat($userData->posts()->status()->count());
        $userData->follower_count = thousandsFormat($userData->follower()->count());
        $userData->following_count = thousandsFormat($userData->following()->count());
        $userData->is_user = $userData->hasRole('user');
        $userData->is_creator = $userData->hasRole('creator');
        $userData->is_agent = $userData->hasRole('agent');
        $userData->profile_link = $profileLink;
        $userData->referral_link = $referralLink;
        $userData->paytm_merchant_id = config('constant.paytm_merchant_id');
        $userData->paytm_merchant_key = config('constant.paytm_merchant_key');
        $userData->paytm_website = config('constant.paytm_website');
        $userData->paytm_callback = config('constant.paytm_callback');
        $userData->razorpay_key = config('constant.razorpay_key');
        $userData->razorpay_secret = config('constant.razorpay_secret');
        $userData->is_razorpay = config('constant.is_razorpay');
        $userData->is_paytm = config('constant.is_paytm');

        $userData->load('taluka');

        $response['data'] = $userData;

        return response($response);
    }

    /**
    * Edit User Profile
    *
    * Developed By : Harshil Rajpura
    * Developed On : 17/05/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/edit-user-profile",
    *      operationId="editUserProfile",
    *      tags={"User"},
    *      summary="Edit User Profile",
    *      description="Edit User Profile",
    *      security={ {"bearerAuth": {} }},
    *      @OA\RequestBody(
    *      @OA\MediaType(
    *           mediaType="multipart/form-data",
    *           @OA\Schema(
    *          @OA\Property(property="first_name", type="string", example="xyz"),
    *          @OA\Property(property="last_name", type="string", example="xyz"),
    *          @OA\Property(property="username", type="string", example="xyz123"),
    *          @OA\Property(property="gender", type="string", example="male"),
    *          @OA\Property(property="profession", type="string", example="Bike rider expert"),
    *          @OA\Property(property="email", type="string", example="xyz@example.com"),
    *          @OA\Property(property="contact_number", type="string", example="+919712585432"),
    *          @OA\Property(property="whatsapp_number", type="string", example="+919712585432"),
    *          @OA\Property(property="about_me", type="string", example="I am bike rider expert from india."),
    *               @OA\Property(
    *                  property="profile_picture",
    *                  type="file",
    *               ),
    *           ),
    *       ),
    *   ),
    *
    * @OA\Response(
    *    response=200,
    *    description="Success"
    *     ),
    * @OA\Response(
    *    response=401,
    *    description="Returns when user is not authenticated",
    *    @OA\JsonContent(
    *       @OA\Property(property="message", type="string", example="Not authorized"),
    *    )
    * ),
    * @OA\Response(response=400, description="Bad request"),
    * )
    */
    public function editUserProfile(Request $request)
    {
        $response = ['status' => true, 'data' => (object)[], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:tbl_users,username,'.auth()->user()->iUserId.',iUserId,deleted_at,NULL',
            'email' => 'required:email|string|email|max:255|unique:tbl_users,vEmail,'.auth()->user()->iUserId.',iUserId,deleted_at,NULL',
            'gender' => 'required',
            'profession' => 'required|string|max:255',
            'contact_number' => 'required|unique:tbl_users,vPhoneNumber,'.auth()->user()->iUserId.',iUserId,deleted_at,NULL',
            'whatsapp_number' => 'required',
            'about_me' => 'nullable|string',
            'profile_picture' => 'sometimes|nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $userData = auth()->user();

        if (!$userData) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return response($response);
        }

        $fileName ="";
        $inputData['vFirstName'] = $request->first_name;
        $inputData['vLastName'] = $request->last_name;
        $inputData['username'] = $request->username;
        $inputData['vEmail'] = $request->email;
        $inputData['eGender'] = $request->gender;
        $inputData['vOccupation'] = $request->profession;
        $inputData['vPhoneNumber'] = $request->contact_number;
        $inputData['whatsapp_number'] = $request->whatsapp_number;
        $inputData['tAboutMe'] = $request->about_me;

        if($request->hasFile('profile_picture') && isset($request->profile_picture) && $request->profile_picture != ""){

            if(fileExists($userData->vImage)) {
                deleteFile($userData->vImage);
            }
            $fileName = storeFile('profile-pictures', $request->profile_picture);
            $inputData['vImage'] = $fileName;
            $userData->vImage = $fileName;
        }

        $userData->where('iUserId', $userData->iUserId)->update($inputData);

        $response['data'] = $userData->refresh();

        return response($response);
    }

    /**
     * Edit User email
     *
     * Developed By : Harshil Rajpura
     * Developed On : 17/05/2022
     */
    /**
     * @OA\Post(
     *      path="/api/v1/edit-user-email",
     *      operationId="editUserEmail",
     *      tags={"User"},
     *      summary="Edit User email",
     *      description="Edit User email",
     *      security={ {"bearerAuth": {} }},
     *      @OA\RequestBody(
     *      @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *               required={"email"},
     *          @OA\Property(property="email", type="string", example="xyz@example.com")
     *       )),
     *   ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * ),
     * @OA\Response(response=400, description="Bad request"),
     * )
     */
    public function editUserEmail(Request $request)
    {
        $response = ['status' => true, 'data' => (object)[], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'email' => 'required:email|string|email|max:255|unique:tbl_users,vEmail,'.auth()->user()->iUserId.',iUserId,deleted_at,NULL',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $userData = auth()->user();

        if (!$userData) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return response($response);
        }

        $inputData['vEmail'] = $request->email;

        $userData->where('iUserId', $userData->iUserId)->update($inputData);

        $response['data'] = $userData->refresh();

        return response($response);
    }

    /**
     *  Follow/Unfollow
     *  Developed By : Kalpesh Joshi
     *  Developed At : 02-06-2022
     *
     *
     */
    /**
     * @OA\Post(
     * path="/api/v1/follow-unfollow",
     * operationId="followUnfollow",
     * tags={"User"},
     * summary="Follow Unfollow",
     * description="Follow Unfollow User - action can be (follow, unfollow)",
     * security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="follow_id",
     *          description="User ID to follow",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="action",
     *          description="Follow or Unfollow",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="follow",
     *              enum={"follow", "unfollow"}
     *          )
     *      ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function followUnfollow(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'follow_id' => 'required:integer',
            'action' => 'in:follow,unfollow'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $user = auth()->user();
        $followId = $request->input('follow_id');
        $action = $request->input('action');

        if($user->iUserId == $followId){
            $response['status'] = false;
            $response['message'] = trans('messages.follow_yourself', ['action' => $action]);
            return $response;
        }

        $follower = User::status()->whereHas(
            'roles', function($q){
                $q->whereIn('name', [config('constant.roles.creator'), config('constant.roles.agent'), config('constant.roles.user')]);
            }
        )->find($followId);

        if (!$follower) {
            $response['status'] = false;
            $response['message'] = trans('messages.user_not_found');
            return $response;
        }

        if ($action == 'follow') {
            $user->following()->sync([$followId], false);
            $response['message'] = trans('messages.you_follow', ['name' => $follower->full_name]);

            //notification to creator
            Notification::send($follower, new FollowUser("You have new follower - ".$user->full_name, $user));

        } else if ($action == 'unfollow') {
            $user->following()->detach($followId);
            $response['message'] = trans('messages.you_unfollow', ['name' => $follower->full_name]);
        }

        return response($response);
    }

    /**
     * Get Popular Creators based on followers
     *
     * Developed By : Amish Soni
     * Developed On : 03/06/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/popular-creators",
     *      operationId="getPopularCreators",
     *      tags={"User"},
     *      summary="Get Popular Creators",
     *      description="Get Popular Creators",
     *      @OA\Parameter(
     *          name="search_term",
     *          description="Search Term/Text",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Page No. (optional) - default 1",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Per Page (optional) - default 15",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     */
    public function getPopularCreators(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'search_term' => 'nullable|min:2'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $userData = User::select('iUserId', 'vFirstName', 'vLastName', 'vEmail', 'vImage')->status()->whereHas(
            'roles', function($q){
                $q->whereIn('name', [config('constant.roles.creator')]);
            }
        );

        if(auth('sanctum')->check()){
            $userData = $userData->where('iUserId','!=',auth('sanctum')->user()->iUserId)
                ->whereDoesntHave('block', function($q) {
                    $q->where('user_id', auth('sanctum')->user()->iUserId);
                });
        }

        if($request->filled('search_term')) {
            $searchTerm = $request->input('search_term');
            $userData = $userData->whereRaw("CONCAT(vFirstName,' ',vLastName) like ?", ["%{$searchTerm}%"]);
        }

        $userData = $userData->withCount('follower')
        ->orderBy('follower_count', 'DESC')
        ->simplePaginate(intval($request->per_page));

        if ($userData->count() == 0) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
        }

        $response['data'] = $userData;

        return response($response);
    }

    /**
     * Get User Dashboard
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 22/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/user-dashboard",
     *      operationId="getUserDashboard",
     *      tags={"User"},
     *      summary="Get User Dashboard",
     *      description="Get User Dashboard",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="filter_by",
     *          description="Filter data based on dates (Optional)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="all",
     *              enum={"today", "yesterday", "7-day", "this-month", "last-month", "all"}
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     */
    public function getUserDashboard(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'filter_by' => 'in:today,yesterday,7-day,this-month,last-month,all'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }


        $userData = User::select('iUserId', 'vFirstName', 'vLastName', 'vEmail', 'vImage')->status()->whereHas(
            'roles', function($q){
                $q->whereIn('name', [config('constant.roles.user')]);
            }
        )
        ->withCount([
            'masterClassPurchased' => function ($query) use ($request) {
                if ($request->filled('filter_by')) {

                    if ($request->filter_by == config('constant.filter_by.today')) {
                        $today = Carbon::now()->format('Y-m-d');
                        $query->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$today."'");
                    }
                    else if ($request->filter_by == config('constant.filter_by.yesterday')) {
                        $yesterday = Carbon::yesterday()->format('Y-m-d');
                        $query->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$yesterday."'");
                    }
                    else if ($request->filter_by == config('constant.filter_by.7-day')) {
                        $today = Carbon::now()->format('Y-m-d');
                        $sevenDays = Carbon::now()->subDays(7)->format('Y-m-d');
                        $query->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') > '".$sevenDays."'");
                        $query->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') <= '".$today."'");
                    }
                    else if ($request->filter_by == config('constant.filter_by.this-month')) {
                        $thisMonth = Carbon::now()->format('Y-m');
                        $query->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$thisMonth."'");
                    }

                    else if ($request->filter_by == config('constant.filter_by.last-month')) {
                        $lastMonth = Carbon::now()->subMonth()->format('Y-m');
                        $query->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$lastMonth."'");
                    }
                    else {
                        $today = Carbon::now()->setTimezone('Asia/Kolkata');
                        $query->whereRaw("CONCAT(master_classes.start_date,' ', master_classes.start_time) >= '".$today."'");
                    }
                }
            },
            'callBooking' => function ($query) use ($request) {
                if ($request->filled('filter_by')) {

                    if ($request->filter_by == config('constant.filter_by.today')) {
                        $today = Carbon::now()->format('Y-m-d');
                        $query->whereRaw("DATE_FORMAT(call_bookings.created_at, '%Y-%m-%d') = '".$today."'");
                    }
                    else if ($request->filter_by == config('constant.filter_by.yesterday')) {
                        $yesterday = Carbon::yesterday()->format('Y-m-d');
                        $query->whereRaw("DATE_FORMAT(call_bookings.created_at, '%Y-%m-%d') = '".$yesterday."'");
                    }
                    else if ($request->filter_by == config('constant.filter_by.7-day')) {
                        $today = Carbon::now()->format('Y-m-d');
                        $sevenDays = Carbon::now()->subDays(7)->format('Y-m-d');
                        $query->whereRaw("DATE_FORMAT(call_bookings.created_at, '%Y-%m-%d') > '".$sevenDays."'");
                        $query->whereRaw("DATE_FORMAT(call_bookings.created_at, '%Y-%m-%d') <= '".$today."'");
                    }
                    else if ($request->filter_by == config('constant.filter_by.this-month')) {
                        $thisMonth = Carbon::now()->format('Y-m');
                        $query->whereRaw("DATE_FORMAT(call_bookings.created_at, '%Y-%m') = '".$thisMonth."'");
                    }
                    else if ($request->filter_by == config('constant.filter_by.last-month')) {
                        $lastMonth = Carbon::now()->subMonth()->format('Y-m');
                        $query->whereRaw("DATE_FORMAT(call_bookings.created_at, '%Y-%m') = '".$lastMonth."'");
                    } else{
                        $today = Carbon::now()->setTimezone('Asia/Kolkata');
                        $query->whereRaw("CONCAT(call_bookings.booking_date,' ', call_bookings.start_time) >= '".$today."'");
                    }
                }
            }
        ])
        ->where('iUserId', auth()->user()->iUserId)
        ->first();

        if ($userData->count() == 0) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
        }

        $response['data'] = $userData;

        return response($response);
    }

    /**
     * Get Creator Dashboard
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 22/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/creator-dashboard",
     *      operationId="getCreatorDashboard",
     *      tags={"User"},
     *      summary="Get Creator Dashboard",
     *      description="Get Creator Dashboard",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="filter_by",
     *          description="Filter data based on dates (Optional)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="all",
     *              enum={"today", "yesterday", "7-day", "this-month", "last-month", "all"}
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     */
    public function getCreatorDashboard(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'filter_by' => 'in:today,yesterday,7-day,this-month,last-month,all'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $userData = User::select('iUserId', 'vFirstName', 'vLastName', 'vEmail', 'vImage')->status()->whereHas(
            'roles', function($q){
                $q->whereIn('name', [config('constant.roles.creator')]);
            }
        )
        ->withCount([
            'creatorCallBooking' => function ($query) use ($request) {
                if ($request->filled('filter_by')) {

                    if ($request->filter_by == config('constant.filter_by.today')) {
                        $today = Carbon::now()->format('Y-m-d');
                        $query->whereRaw("DATE_FORMAT(call_bookings.created_at, '%Y-%m-%d') = '".$today."'");
                    }
                    else if ($request->filter_by == config('constant.filter_by.yesterday')) {
                        $yesterday = Carbon::yesterday()->format('Y-m-d');
                        $query->whereRaw("DATE_FORMAT(call_bookings.created_at, '%Y-%m-%d') = '".$yesterday."'");
                    }
                    else if ($request->filter_by == config('constant.filter_by.7-day')) {
                        $today = Carbon::now()->format('Y-m-d');
                        $sevenDays = Carbon::now()->subDays(7)->format('Y-m-d');
                        $query->whereRaw("DATE_FORMAT(call_bookings.created_at, '%Y-%m-%d') > '".$sevenDays."'");
                        $query->whereRaw("DATE_FORMAT(call_bookings.created_at, '%Y-%m-%d') <= '".$today."'");
                    }
                    else if ($request->filter_by == config('constant.filter_by.this-month')) {
                        $thisMonth = Carbon::now()->format('Y-m');
                        $query->whereRaw("DATE_FORMAT(call_bookings.created_at, '%Y-%m') = '".$thisMonth."'");
                    }
                    else if ($request->filter_by == config('constant.filter_by.last-month')) {
                        $lastMonth = Carbon::now()->subMonth()->format('Y-m');
                        $query->whereRaw("DATE_FORMAT(call_bookings.created_at, '%Y-%m') = '".$lastMonth."'");
                    } else{
                        $today = Carbon::now()->setTimezone('Asia/Kolkata');
                        $query->whereRaw("CONCAT(call_bookings.booking_date,' ', call_bookings.start_time) >= '".$today."'");
                    }
                }
            }
        ])
        ->where('iUserId', auth()->user()->iUserId)
        ->first();

        if ($userData->count() == 0) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
        }

        $masterClasses = $userData->masterClass()->pluck('id')->toArray();

        $masterClassBookings = MasterClassUser::whereIn('master_class_id', $masterClasses)
        ->status(config('constant.status.booked_value'));

        if ($request->filled('filter_by')) {

            if ($request->filter_by == config('constant.filter_by.today')) {
                $today = Carbon::now()->format('Y-m-d');
                $masterClassBookings->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') = '".$today."'");
            }
            else if ($request->filter_by == config('constant.filter_by.yesterday')) {
                $yesterday = Carbon::yesterday()->format('Y-m-d');
                $masterClassBookings->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') = '".$yesterday."'");
            }
            else if ($request->filter_by == config('constant.filter_by.7-day')) {
                $today = Carbon::now()->format('Y-m-d');
                $sevenDays = Carbon::now()->subDays(7)->format('Y-m-d');
                $masterClassBookings->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') > '".$sevenDays."'");
                $masterClassBookings->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') <= '".$today."'");
            }
            else if ($request->filter_by == config('constant.filter_by.this-month')) {
                $thisMonth = Carbon::now()->format('Y-m');
                $masterClassBookings->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = '".$thisMonth."'");
            }
            else if ($request->filter_by == config('constant.filter_by.last-month')) {
                $lastMonth = Carbon::now()->subMonth()->format('Y-m');
                $masterClassBookings->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = '".$lastMonth."'");
            }
        }
        $masterClassBookings = $masterClassBookings->count();

        $myEarningData = PaymentPayout::select('id','user_id')
        ->where('user_id', auth()->user()->iUserId)
        ->orderByDesc('created_at')
        ->filter($request);

        $myGiftData = Gift::select('id','user_id','creator_id')
        ->where('creator_id', auth()->user()->iUserId)
        ->orderByDesc('created_at')
        ->filter($request);

        $myAffiliationData = PaymentPayout::select('id','user_id')
        ->where('user_id', auth()->user()->iUserId)
        ->where('module_name', 'master_class_affiliate')
        ->orderByDesc('created_at')
        ->filter($request);

        $userData->my_earning = (string)$myEarningData->sum('payout_amount');
        $userData->my_gift = (string)$myGiftData->sum('amount');
        $userData->my_affiliation = (string)$myAffiliationData->sum('payout_amount');
        $userData->product_sale = "Coming Soon";
        $userData->company_creator_fund = "Coming Soon";
        $userData->master_class_count = $masterClassBookings;
        $response['data'] = $userData;

        return response($response);
    }

    /**
     * My Purchased Master Class
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 25/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/my-purchased-master-class",
     *      operationId="myPurchasedMasterClass",
     *      tags={"User"},
     *      summary="Get User Purchased Master Class",
     *      description="Get User Purchased Master Class",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="page",
     *          description="Page No. (optional) - default 1",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Per Page (optional) - default 15",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     */
    public function myPurchasedMasterClass(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $userData = Auth::user()->masterClassPurchased()->orderBy('start_date')
        ->with('categories')
        ->simplePaginate(intval($request->per_page));

        if(!$userData) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return $response;
        }

        $response['data'] = $userData;

        return response($response);
    }

    /**
     * View Notifications
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 26/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/view-notifications",
     *      operationId="viewNotifications",
     *      tags={"User"},
     *      summary="Get User Notifications List",
     *      description="Get User Notifications List",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="page",
     *          description="Page No. (optional) - default 1",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Per Page (optional) - default 15",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     */
    public function viewNotifications(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        //Code added to update dates format with timezone as simplePaginate was not working with resource and collection
        $notificationData = tap(auth()->user()->notifications()->simplePaginate(intval($request->per_page)),function($paginatedInstance){
            return $paginatedInstance->getCollection()->transform(function ($value) {
                return [
                    'id' => $value->id,
                    'type' => $value->type,
                    'notifiable_type' => $value->notifiable_type,
                    'notifiable_id' => $value->notifiable_id,
                    'data' => $value->data,
                    'read_at' => $value->read_at,
                    'created_at' => Carbon::createFromFormat('Y-m-d H:i:s',$value->created_at)->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::createFromFormat('Y-m-d H:i:s',$value->updated_at)->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s'),
                ];
            });
        });

        if($notificationData->count() == 0) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
        }

        $response['data'] = $notificationData;

        return response($response);
    }

    /**
     * Save user location
     *
     * Developed By : Amish Soni
     * Developed On : 23/09/2022
     */
    /**
     * @OA\Post(
     * path="/api/v1/phone-exist",
     * operationId="phone-exist",
     * tags={"User"},
     * summary="Check Phone Exist",
     * description="Check Phone Exist",
     * @OA\RequestBody(
     *  @OA\MediaType(
     *      mediaType="application/json",
     *      @OA\Schema(
     *        required={"phone_number"},
     *        @OA\Property(property="phone_number", type="string", example="9999999999"),
     *      ),
     *  ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success"
     * ),
     *
     *
     * )
     */
    public function checkPhoneExist(Request $request)
    {
        $response = ['status' => true, 'data' => (object)[], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|min:8|max:13',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $userData = User::where('vPhoneNumber', $request->phone_number)->status()->first();

        if (!$userData) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return response($response);
        }

        $response['data'] = $userData;


        return response($response);
    }

    /**
     * Update user password
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 23/09/2022
     */
    /**
     * @OA\Post(
     * path="/api/v1/update-password",
     * operationId="updatePassword",
     * tags={"User"},
     * summary="Update user password",
     * description="Update user password",
     * @OA\RequestBody(
     *  @OA\MediaType(
     *      mediaType="application/json",
     *      @OA\Schema(
     *        required={"phone_number","password"},
     *        @OA\Property(property="phone_number", type="string", example="999999999",description="Phone number of the user"),
     *              @OA\Property(property="password", type="string", format="password", example="secret",description="Password"),
     *      ),
     *  ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success"
     * ),
     *
     *
     * )
     */
    public function updatePassword(Request $request)
    {
        $response = ['status' => true, 'data' => (object)[], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|min:8|max:13',
            'password'     => 'required|min:5|max:25',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $userData = User::where('vPhoneNumber', $request->phone_number)->status()->first();

        if (!$userData) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return response($response);
        }

        $data = [];

        if($request->filled('password')) {
            $data['vPassword'] = $request->password;
        }

        $userData->update($data);

        $response['data'] = $userData;

        return response($response);
    }

    /**
     * Get User Followers
     *
     * Developed By : Amish Soni
     * Developed On : 03/10/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/user-followers",
     *      operationId="getUserFollowers",
     *      tags={"User"},
     *      summary="Get User Followers",
     *      description="Get User Followers",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="user_id",
     *          description="User Id (optional) - If not passed then loggedin user id will be used",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     */
    public function getUserFollowers(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        if($request->filled('user_id')) {
            $userData = User::select(['iUserId', 'vFirstName', 'vLastName', 'vImage'])->where('iUserId', $request->user_id)->with(['follower:iUserId,vFirstName,vLastName,vImage'])->status()->first();
        } else {
            $userData = auth()->user()->load(['follower:iUserId,vFirstName,vLastName,vImage']);
        }

        if(!$userData) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return $response;
        }

        $response['data'] = $userData;

        return response($response);
    }

    /**
     * Get User Following
     *
     * Developed By : Amish Soni
     * Developed On : 03/10/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/user-following",
     *      operationId="getUserFollowing",
     *      tags={"User"},
     *      summary="Get User Following",
     *      description="Get User Following",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="user_id",
     *          description="User Id (optional) - If not passed then loggedin user id will be used",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     */
    public function getUserFollowing(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        if($request->filled('user_id')) {
            $userData = User::select(['iUserId', 'vFirstName', 'vLastName', 'vImage'])->where('iUserId', $request->user_id)->with(['following:iUserId,vFirstName,vLastName,vImage'])->status()->first();
        } else {
            $userData = auth()->user()->load(['following:iUserId,vFirstName,vLastName,vImage']);
        }

        if(!$userData) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return $response;
        }

        $response['data'] = $userData;

        return response($response);
    }

    /**
     * Get User Sub Tree
     *
     * Developed By : Amish Soni
     * Developed On : 06/10/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/user-subtree",
     *      operationId="getUserSubTree",
     *      tags={"User"},
     *      summary="Get User Sub Tree",
     *      description="Get User Sub Tree",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="user_id",
     *          description="User Id (optional) - If not passed then loggedin user id will be used",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="role",
     *          description="User Role",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *               default="creator",
     *               enum={"creator", "agent", "user"}
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Page No. (optional) - default 1",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Per Page (optional) - default 15",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     */
    public function getUserSubTree(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|integer',
            'role' => 'required|in:'.config('constant.roles.creator').','.config('constant.roles.agent').','.config('constant.roles.user'),
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $userId = auth()->user()->iUserId;
        if($request->filled('user_id')) {
            $userId = $request->user_id;
        }

        $role = $request->role;

        $userData = User::select(['iUserId', 'vFirstName', 'vLastName', 'vImage'])
        ->whereHas(
            'roles', function($q) use($role){
                $q->where('name', $role);
            }
        )
        ->where('iUserId', '!=', $userId)
        ->where(function($query) use($userId) {
            $query->where('vTeamId', 'like', '%-'.$userId)
                ->orWhere('vTeamId', 'like', $userId.'-%')
                ->orWhere('vTeamId', 'like', '%-'.$userId.'-%')
                ->orWhere('vTeamId', $userId);
        })
        ->status()
        ->simplePaginate(intval($request->per_page));

        if(!$userData) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return $response;
        }

        $response['data'] = $userData;

        return response($response);
    }

    /**
     *  Block User
     *  Developed By : Amish Soni
     *  Developed At : 03-11-2022
     *
     *
     */
    /**
     * @OA\Post(
     * path="/api/v1/block-user",
     * operationId="blockUser",
     * tags={"User"},
     * summary="Block User",
     * description="Block User - action can be (block, unblock)",
     * security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="user_id",
     *          description="User ID to block/unblock",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="action",
     *          description="Block or Unblock",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="block",
     *              enum={"block", "unblock"}
     *          )
     *      ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function blockUser(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'user_id' => 'required:integer',
            'action' => 'in:block,unblock'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $user = auth()->user();
        $userId = $request->input('user_id');
        $action = $request->input('action');

        $userData = User::status()->find($userId);

        if (!$userData) {
            $response['status'] = false;
            $response['message'] = trans('messages.user_not_found');
            return $response;
        }

        if ($user->iUserId == $userId) {
            $response['status'] = false;
            $response['message'] = trans('messages.user_self_block');
            return $response;
        }

        if ($action == 'block') {
            $userData->block()->sync([$user->iUserId], false);
            $response['message'] = trans('messages.block_submitted');
        } else if ($action == 'unblock') {
            $userData->block()->detach($user->iUserId);
            $response['message'] = trans('messages.unblock_submitted');
        }

        return response($response);
    }
}
