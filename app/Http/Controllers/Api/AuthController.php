<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\User;
use App\Models\TempUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['socialLogin', 'registerUser', 'login', 'registerViaPhone']]);
    }

    /**
     * Normal Login API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     * path="/api/login",
     * operationId="login",
     * tags={"Authentication"},
     * summary="Normal Login",
     * description="Normal Login",
     *      @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              required={"phone_number","password"},
     *              @OA\Property(property="phone_number", type="string", example="999999999",description="Phone number of the user"),
     *              @OA\Property(property="password", type="string", format="password", example="secret",description="Password"),
     *           ),
     *       ),
     *   ),
     *
     * @OA\Response(
     *    response=422,
     *    description="Validator Error"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Authentication Error"
     *     ),
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * )
     */
    public function login(Request $request)
    {
        $response = ['status' => true, 'data' => (object)[], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|integer',
            'password'     => 'required|min:5'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;

            return response($response, 422);
        }

        $user = User::where(['vPhoneNumber' => request('phone_number')])
        ->where('vPassword', request('password'))
        ->first();

        if (!$user) {
            $response['message'] = trans('messages.invalid_login');
            $response['status'] = false;
            return response($response, 200);
        }

        if($user && $user->status != config('constant.status.active_value')){
            $response['message'] = trans('messages.account_blocked');
            $response['status'] = false;
            return response($response, 200);
        }

        $user->loadCount(['posts'=> function ($query) {
            $query->status();
        }]);
        // $user->wallet->refreshBalance();     
        // $user->wallet_balance = $user->balanceFloat;
        $user->is_user = $user->hasRole('user');
        $user->is_creator = $user->hasRole('creator');
        $user->is_agent = $user->hasRole('agent');
        $user->paytm_merchant_id = config('constant.paytm_merchant_id');
        $user->paytm_merchant_key = config('constant.paytm_merchant_key');
        $user->paytm_website = config('constant.paytm_website');
        $user->paytm_callback = config('constant.paytm_callback');
        $user->razorpay_key = config('constant.razorpay_key');
        $user->razorpay_secret = config('constant.razorpay_secret');
        $user->is_razorpay = config('constant.is_razorpay');
        $user->is_paytm = config('constant.is_paytm');

        $user->load('taluka');

        $token = $user->createToken('auth_token')->plainTextToken;

        $response['access_token'] = $token;
        $response['token_type'] = 'Bearer';
        $response['data'] = $user;
            
        return response($response);
    }

    /**
     * Login social login
     *
     * Developed By : Amish Soni
     * Developed On : 26/05/2022
     */
    /**
     * @OA\Post(
     * path="/api/social-login",
     * operationId="socialLogin",
     * tags={"Authentication"},
     * summary="Social Login",
     * description="Social Login",
     *      @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             required={"provider", "provider_id", "first_name", "last_name", "provider_token"},
     *              @OA\Property(property="provider", type="string", example="google",description="Provider Name"),
     *              @OA\Property(property="email", type="string", example="",description="Email"),
     *              @OA\Property(property="contact_number", type="string", example="",description="contact_number"),
     *              @OA\Property(property="provider_id", type="string", example="",description="provider id"),
     *              @OA\Property(property="first_name", type="string", example="First name",description="first name"),
     *              @OA\Property(property="last_name", type="string", example="Last name",description="last name"),
     *              @OA\Property(property="profile_picture", type="", example=""),
     *              @OA\Property(property="provider_token", type="string", example="",description="provider token"),
     *              @OA\Property(property="referral_code", type="string", example="",description="provider token"),
     *           ),
     *       ),
     *   ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * )
     */
    public function socialLogin(Request $request)
    {
        $response = ['status' => true, 'data' => (object)[], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'provider' => 'required',
            'email' => 'required_without:contact_number|email',
            'contact_number' => 'required_without:email',
            'provider_id' => 'required',
            'first_name' => 'required',
            'profile_picture' => 'nullable|url',
            'referral_code' => 'nullable',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        Log::info(json_encode($request->all()));
        
        $provider = $request->provider;
        $provider_id = $request->provider_id ?? null;
        $first_name = $request->first_name ?? null;
        $last_name = $request->last_name ?? null;
        $provider_token = $request->provider_token ?? null;

        $userData = [
            'vFirstName' => $first_name,
            'vLastName' => $last_name,
        ];

        $user = User::whereIn('status',[config('constant.status.active_value'),config('constant.status.inactive_value')]);
        
        if($request->filled('email')) {
            $email = $request->email;
            $userData['vEmail'] = $email;
            $user->where('vEmail',$email);
        }

        if($request->filled('contact_number') && $request->contact_number != 'null') {
            $contact_number = $request->contact_number;
            $userData['vPhoneNumber'] = $contact_number;
            if($request->filled('email')) {
                $user->orWhere('vPhoneNumber',$contact_number);
            }else{
                $user->where('vPhoneNumber',$contact_number);
            }
        }

        if($request->filled('referral_code')) {
            $referral_code = $request->referral_code;
            $referralUser = User::select('iUserId', 'vPhoneNumber')->where('vMyCode', $referral_code)->first();
            
            if($referralUser) {
                $userData['vReferCode'] = $referralUser->vPhoneNumber;
                $userData['iReferUserId'] = $referralUser->iUserId;
            }
        }

        $user = $user->first();

        if ($user && $user->status == config('constant.status.inactive_value')) {
            $response['status'] = false;
            $response['message'] = trans('messages.account_blocked');

            return response($response, 200);
        }
        
        if(!$user){
            $userData['vMyCode'] = generateReferralCode(12);
            $user = User::create($userData);
        }else{
            //$user->update($userData);
        }
        if ($user) {
            if(!$user->vImage) {
                if($request->filled('profile_picture')) {
                    $user->vImage = $request->profile_picture;
                    $user->save();
                }
            }
            $role = Role::where(['name'=>config('constant.roles.user')])->first();
            if ($role) {
                $user->assignRole($role);
            }
        }

        $checkSAData = [
            'provider_name' => $provider,
            'provider_id' => $provider_id,
            'user_id' => $user->iUserId
        ];
        $user->socialAccounts()->updateOrCreate($checkSAData, [
            'user_id' => $user->iUserId,
            'provider_id' => $provider_id,
            'provider_name' => $provider,
            'provider_token' => $provider_token,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        
        $user->refresh();
        $user->loadCount(['posts'=> function ($query) {
            $query->status();
        }]);
        $user->wallet->refreshBalance();     
        $user->wallet_balance = $user->balanceFloat;
        $user->is_user = $user->hasRole('user');
        $user->is_creator = $user->hasRole('creator');
        $user->is_agent = $user->hasRole('agent');
        $user->paytm_merchant_id = config('constant.paytm_merchant_id');
        $user->paytm_merchant_key = config('constant.paytm_merchant_key');
        $user->paytm_website = config('constant.paytm_website');
        $user->paytm_callback = config('constant.paytm_callback');
        $user->razorpay_key = config('constant.razorpay_key');
        $user->razorpay_secret = config('constant.razorpay_secret');
        $user->is_razorpay = config('constant.is_razorpay');
        $user->is_paytm = config('constant.is_paytm');

        $user->load('taluka');

        $response['access_token'] = $token;
        $response['token_type'] = 'Bearer';
        $response['data'] = $user;
            
        return response($response);
    }

    /**
     * Handle request to logout current user
     *
     */
    /**
     * @OA\Post(
     * path="/api/v1/logout",
     * operationId="logout",
     * tags={"Authentication"},
     * summary="Logout",
     * description="Logout",
     * security={ {"bearerAuth": {} }},
     *
     * @OA\Response(
     *    response=401,
     *    description="Token Error"
     *     ),
     *
     * )
     */
    public function logout(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        
        Auth::user()->tokens()->delete();

        $response['message'] = trans('auth.logout');

        return response($response);
    }

    /**
     * Register
     *
     * Developed By : Amish Soni
     * Developed On : 30/05/2022
     */
    /**
     * @OA\Post(
     * path="/api/register-user",
     * operationId="registerUser",
     * tags={"Authentication"},
     * summary="Creator/ Agent Registration",
     * description="Creator/ Agent Registration",
     *      @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             required={"profile_type", "first_name", "last_name", "email", "contact_number", "whatsapp_number", "user_id"},
     *              @OA\Property(property="profile_type", type="string", example="creator",description=""),
     *              @OA\Property(property="user_id", type="integer", example=""),
     *              @OA\Property(property="first_name", type="string", example="First Name",description=""),
     *              @OA\Property(property="last_name", type="string", example="Last Name",description=""),
     *              @OA\Property(property="email", type="string", example="John@riser.in",description=""),
     *              @OA\Property(property="contact_number", type="string", example="9812345678"),
     *              @OA\Property(property="whatsapp_number", type="string", example="9812345678"),
     *              @OA\Property(property="user_skills", type="string", example=""),
     *              @OA\Property(property="user_experience", type="string", example=""),
     *              @OA\Property(property="business_name", type="", example=""),
     *              @OA\Property(property="about_me", type="string", example=""),
     *              @OA\Property(property="facebook_link", type="string", example=""),
     *              @OA\Property(property="twitter_link", type="string", example=""),
     *              @OA\Property(property="linkedin_link", type="string", example=""),
     *              @OA\Property(property="instagram_link", type="string", example=""),
     *              @OA\Property(property="youtube_link", type="string", example=""),
     *              @OA\Property(property="referral_code", type="string", example=""),
     *           ),
     *       ),
     *   ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * )
     */

    public function registerUser(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'profile_type' => 'in:creator,agent',
            'user_id' => 'required',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:tbl_users,vEmail,'.$request->user_id.',iUserId,deleted_at,NULL',
            'contact_number' => 'required|min:8|max:13|unique:tbl_users,vPhoneNumber,'.$request->user_id.',iUserId,deleted_at,NULL',
            'whatsapp_number' => 'required|min:8|max:13|unique:tbl_users,whatsapp_number,'.$request->user_id.',iUserId,deleted_at,NULL',
            'facebook_link' => 'nullable|url',
            'twitter_link' => 'nullable|url',
            'linkedin_link' => 'nullable|url',
            'instagram_link' => 'nullable|url',
            'youtube_link' => 'nullable|url',
            'referral_code' => 'nullable',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }
        
        $inputData = $request->all();
        $user = User::where('iUserId', $inputData['user_id'])->status()->first();

        if (!$user) {
            $response['status'] = false;
            $response['message'] = trans('messages.user_not_found');

            return response($response, 200);
        }

        $profileType = $inputData['profile_type'];
        $userId = $user->iUserId;
        
        $tempUser = TempUser::where('user_id', $userId)
        ->where('user_role', $profileType)
        ->whereNull('user_status')->first();

        if($tempUser) {
            $response['status'] = false;
            $response['message'] = trans('messages.already_has_role', ['role' => $profileType]);

            return response($response, 200);
        }

        $inputData['user_id'] = $userId;
        $inputData['user_role'] = $profileType;

        if($request->filled('referral_code')) {
            $referral_code = $request->referral_code;
            $referralUser = User::select('iUserId', 'vPhoneNumber')->where('vMyCode', $referral_code)->first();
            if($referralUser) {
                $inputData['refer_code'] = $referralUser->vPhoneNumber;
                $inputData['refer_user_id'] = $referralUser->iUserId;
                $inputData['team_id'] = $referralUser->vTeamId;
            }
        }

        $tempUser = TempUser::create($inputData);

        $response['data'] = $user;

        return response($response);
    }

    /**
     * Register via phone
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 24/08/2022
     */
    /**
     * @OA\Post(
     * path="/api/register-via-phone",
     * operationId="registerViaPhone",
     * tags={"Authentication"},
     * summary="Registration via phone number",
     * description="Registration via phone number",
     *      @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             required={"phone_number"},
     *              @OA\Property(property="phone_number", type="string", example="9812345678"),
     *              @OA\Property(property="referral_code", type="string", example=""),
     *           ),
     *       ),
     *   ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * )
     */

    public function registerViaPhone(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|min:8|max:13|unique:tbl_users,vPhoneNumber,'.$request->user_id.',iUserId,deleted_at,NULL',
            'referral_code' => 'nullable',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $user = new User();
        $user->vFirstName = $request->phone_number;
        $user->vLastName = $request->phone_number;
        $user->vEmail = $request->phone_number.'@'.config('app.url');
        $user->vPhoneNumber = $request->phone_number;
        $user->vMyCode = generateReferralCode(12);

        if($request->filled('referral_code')) {
            $referral_code = $request->referral_code;
            $referralUser = User::select('iUserId', 'vPhoneNumber')->where('vMyCode', $referral_code)->first();
            
            if($referralUser) {
                $user->vReferCode = $referralUser->vPhoneNumber;
                $user->iReferUserId = $referralUser->iUserId;
                $user->vTeamId = $referralUser->vTeamId != "" ? $referralUser->vTeamId."-".$referralUser->iUserId : $referralUser->iUserId;
            }
        }

        $user->save();

        $role = Role::where(['name'=>config('constant.roles.user')])->first();
        if ($role) {
            $user->assignRole($role);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        
        $user->refresh();
        $user->wallet->refreshBalance();     
        $user->wallet_balance = $user->balanceFloat;
        $user->is_user = $user->hasRole('user');
        $user->is_creator = $user->hasRole('creator');
        $user->is_agent = $user->hasRole('agent');
        $user->paytm_merchant_id = config('constant.paytm_merchant_id');
        $user->paytm_merchant_key = config('constant.paytm_merchant_key');
        $user->paytm_website = config('constant.paytm_website');
        $user->paytm_callback = config('constant.paytm_callback');
        $user->razorpay_key = config('constant.razorpay_key');
        $user->razorpay_secret = config('constant.razorpay_secret');
        $user->is_razorpay = config('constant.is_razorpay');
        $user->is_paytm = config('constant.is_paytm');

        $response['access_token'] = $token;
        $response['token_type'] = 'Bearer';
        $response['data'] = $user;
            
        return response($response);
    }
}