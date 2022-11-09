<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\CallBooking;
use App\Models\MasterClass;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Services\WebexService;
use App\Models\MasterClassUser;
use App\Models\MasterCategories;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\MasterClassPromoter;
use App\Http\Controllers\Controller;
use App\Notifications\BeforeMasterClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Offlineagency\LaravelWebex\LaravelWebex;

class MasterClassController extends Controller
{
    /**
    * Create Master Class
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 05/07/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/create-master-class",
    *      operationId="createMasterClass",
    *      tags={"Master Class"},
    *      summary="Create Master Class",
    *      description="Create Master Class API",
    *      security={ {"bearerAuth": {} }},
    *      @OA\RequestBody(
    *      @OA\MediaType(
    *           mediaType="multipart/form-data",
    *           @OA\Schema(
    *               required={"title", "category_id", "start_date", "start_time", "amount", "banner_image"},
    *               @OA\Property(
    *                   property="title",
    *                   type="string",
    *                   example="This is title for master class"
    *               ),
    *               @OA\Property(
    *                   property="category_id",
    *                   description="Category IDs if multiple send with comma seperated",
    *                   example="1,2",
    *                   type="string"
    *               ),
    *               @OA\Property(
    *                   property="start_date",
    *                   type="string",
    *                   format="date",
    *                   example="2022-07-05"
    *               ),
    *               @OA\Property(
    *                   property="start_time",
    *                   type="string",
    *                   example="01:30 PM"
    *               ),
    *               @OA\Property(
    *                   property="amount",
    *                   type="number",
    *                   format="double",
    *                   example="1000"
    *               ),
    *               @OA\Property(
    *                   property="banner_image",
    *                   type="file",
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
    public function createMasterClass(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        $fileName = null;

        $validator = Validator::make($request->all(), [
            'banner_image' => 'required|image|max:5120',
            'title'        => 'required|string|max:255',
            'category_id'  => 'required|string',
            'start_date'   => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time'   => 'required|date_format:h:i A',
            'amount'       => 'required|gte:1|regex:/^\d{0,10}(\.\d{1,2})?$/',
            // 'webex_auth_code' => 'required|string'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        //validation for past date & time
        $todayDateTime = Carbon::now('Asia/Kolkata')->format('Y-m-d h:i A');
        $bookingDate = $request->start_date;
        $bookingTime = $request->start_time;
        $bookingDateTime = $bookingDate.' '.$bookingTime;
        $date1 = Carbon::createFromFormat('Y-m-d h:i A', $bookingDateTime);
        $date2 = Carbon::createFromFormat('Y-m-d h:i A', $todayDateTime);

        if ($date1->lt($date2)) {
            $response['status'] = false;
            $response['message'] = trans('messages.after_date',['date' => $date2->format('d-m-Y h:i A')]);

            return response($response, 200);
        }

        $request->start_time = Carbon::parse($request->start_time)->format('H:i');
        $duration = $request->duration ?? 30;

        if (empty(auth()->user()->vEmail) || (!filter_var(auth()->user()->vEmail, FILTER_VALIDATE_EMAIL))){
            $response['status'] = false;
            $response['message'] = trans('messages.invalid_email');
            return response($response);
        }

        try {
            // $webexMeeting = WebexService::createMeeting($request->get('webex_auth_code'), ['title' => $request->title, 'start_date' => $request->start_date, 'start_time' => $request->start_time, 'duration' => $duration]);
            if($request->hasFile('banner_image')){
                $fileName = storeFile('master-class', $request->banner_image);
            }
            $masterClassData = new MasterClass();
            $masterClassData->user_id = auth()->user()->iUserId;
            $masterClassData->title = $request->title;
            $masterClassData->banner_image = $fileName;
            $masterClassData->start_date = $request->start_date;
            $masterClassData->start_time = $request->start_time;
            $masterClassData->amount = $request->amount;
            // $masterClassData->meeting_link = $webexMeeting['meeting']->webLink;
            // $masterClassData->meeting_id = $webexMeeting['meeting']->id;
            // $masterClassData->refresh_token = $webexMeeting['refresh_token'] ?? null;
            $masterClassData->duration = $duration;
            $masterClassData->save();

            if($request->filled('category_id')){
                $categoryIds = explode(',', $request->category_id);
                $masterClassData->categories()->sync($categoryIds);
            }

            $response['data'] = $masterClassData;
        } catch (Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
        }

        return response($response);
    }

    /**
     * Get Master Class List
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 05/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/master-class-list",
     *      operationId="getMasterClassList",
     *      tags={"Master Class"},
     *      summary="Get Master Class List",
     *      description="Returns list of Master Class",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="show_old",
     *          description="Display Old Data",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="boolean"
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
     *      @OA\Parameter(
     *          name="user_id",
     *          description="User Id (optional) - default logged in user",
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
     * Returns list of Posts
     */
    public function getMasterClassList(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $userId = auth()->user()->iUserId;

        if($request->filled('user_id')){
            $userId = $request->user_id;
        }

        $today = Carbon::now()->setTimezone('Asia/Kolkata');

        $old_data = ($request->filled('show_old') && $request->show_old == 'true');

        $promotedMasterClassData = MasterClassPromoter::where('user_id', $request->user_id ?? auth()->user()->iUserId)->get()->pluck('master_class_id')->toArray();

        $masterClassData = MasterClass::select(['id', 'user_id', 'title', 'banner_image', 'start_date', 'start_time', 'amount','duration', 'is_updatable', 'is_master_class_started', 'is_master_class_ended', 'meeting_link', 'meeting_id', DB::raw('CONCAT(start_date," ", start_time) AS master_class_time')])
        ->with(['user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id','user.country','user.state', 'user.district', 'user.taluka','categories:id,category_name'])
        ->where('user_id', $userId);

        if(!$old_data) {
            $masterClassData->having('master_class_time', '>=', $today)
            ->orderBy('start_date');
        }else{
            $masterClassData->orderByDesc('start_date');
        }

        //block user's master classes will not display
        if(auth('sanctum')->check()){
            $masterClassData->whereHas('user', function ($query) {
                $query->whereDoesntHave('block', function($q) {
                    $q->where('user_id', auth('sanctum')->user()->iUserId);
                });
            });
        }

        $masterClassData = $masterClassData->orWhereIn('id', $promotedMasterClassData)
        ->status()
        ->simplePaginate(intval($request->per_page));

        $response['data'] = $masterClassData ?? [];

        return response($response);
    }

    /**
    * Edit Master Class
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 05/07/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/edit-master-class",
    *      operationId="editMasterClass",
    *      tags={"Master Class"},
    *      summary="Edit Master Class",
    *      description="Edit Master Class",
    *      security={ {"bearerAuth": {} }},
    *      @OA\RequestBody(
    *      @OA\MediaType(
    *           mediaType="multipart/form-data",
    *           @OA\Schema(
    *               required={"id", "title", "category_id", "start_date", "start_time"},
    *               @OA\Property(
    *                   property="id",
    *                   type="number",
    *                   example="1"
    *               ),
    *               @OA\Property(
    *                   property="title",
    *                   type="string",
    *                   example="This is title for master class"
    *               ),
    *               @OA\Property(
    *                   property="category_id",
    *                   description="Category IDs if multiple send with comma seperated",
    *                   example="1,2",
    *                   type="string"
    *               ),
    *               @OA\Property(
    *                   property="start_date",
    *                   type="string",
    *                   format="date",
    *                   example="2022-07-05"
    *               ),
    *               @OA\Property(
    *                   property="start_time",
    *                   type="string",
    *                   example="01:30 PM"
    *               ),
    *               @OA\Property(
    *                   property="banner_image",
    *                   type="file",
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
    public function editMasterClass(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'id'           => 'required|numeric',
            'banner_image' => 'sometimes|required|image|max:5120',
            'title'        => 'required|string|max:255',
            'category_id'  => 'required|string',
            'start_date'   => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time'   => 'required|date_format:h:i A',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $loginId = auth()->user()->iUserId;

        $masterClassData = MasterClass::where('id', $request->id)
        ->where('user_id', $loginId)
        ->with('categories')->first();

        if (!$masterClassData) {
            $response['status'] = false;
            $response['message'] = trans('messages.not_authorized');
            return response($response);
        }


        $fileName ="";
        $inputData = $request->all();
        $inputData['start_time'] = Carbon::parse($inputData['start_time'])->format('H:i');
        if($request->hasFile('banner_image') && isset($request->banner_image) && $request->banner_image != ""){

            if(fileExists($masterClassData->banner_image)) {
                deleteFile($masterClassData->banner_image);
            }
            $fileName = storeFile('master-class', $request->banner_image);
            $inputData['banner_image'] = $fileName;
            $masterClassData->banner_image = $fileName;
        }

        if($request->filled('category_id')){
            $categoryIds = explode(',', $request->category_id);
            $masterClassData->categories()->sync($categoryIds);
        }

        $masterClassData->is_updatable = 0;
        $masterClassData->updated_by = $loginId;

        if($masterClassData->update($inputData)) {
            //notification to all booking users
            Notification::send($masterClassData->bookingUsers, new BeforeMasterClass("Master class - ".$masterClassData->title." has been changed", $masterClassData));
        }


        $response['data'] = $masterClassData->refresh();

        return response($response);
    }

    /**
    * Delete Master Class
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 05/07/2022
    */
    /**
    * @OA\Delete(
    *       path="/api/v1/delete-master-class",
    *       operationId="deleteMasterClass",
    *       tags={"Master Class"},
    *       summary="Delete Master Class",
    *       description="Delete Master Class",
    *       security={ {"bearerAuth": {} }},
    *       @OA\RequestBody(
    *       @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(
    *               required={"id"},
    *               @OA\Property(
    *                   property="id",
    *                   type="integer",
    *                   example="1"
    *               ),
    *           ),
    *       ),
    *       ),
    *      @OA\Response(
    *          response=200,
    *          description="Success"
    *       ),
    *       @OA\Response(response=400, description="Bad request"),
    *     )
    *
    * Returns list of Posts
    */
    public function deleteMasterClass(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $subscribedExist = MasterClassUser::where('master_class_id', $request->id)->exists();
        if ($subscribedExist){
            $response['status'] = false;
            $response['message'] = trans('messages.can_not_delete_master_class');
            return response($response);
        }

        $masterClassData = MasterClass::where('id', $request->id)
        ->where('user_id', auth()->user()->iUserId)
        ->delete();

        if (!$masterClassData) {
            $response['status'] = false;
            $response['message'] = trans('messages.not_authorized');
            return response($response);
        }

        return response($response);
    }

    /**
    * Enroll Master Class
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 07/07/2022
    */
    /**
    * @OA\Post(
    *       path="/api/v1/enroll-master-class",
    *       operationId="enrollMasterClass",
    *       tags={"Master Class"},
    *       summary="Enroll Master Class",
    *       description="Enroll Master Class",
    *       security={ {"bearerAuth": {} }},
    *       @OA\RequestBody(
    *       @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(
    *               required={"payment_gateway","transaction_id"},
    *               @OA\Property(
    *                   property="master_class_id",
    *                   type="integer",
    *                   example="1"
    *               ),
    *               @OA\Property(
    *                   property="promoter_id",
    *                   type="integer",
    *                   example="1"
    *               ),
    *               @OA\Property(
    *                   property="payment_gateway",
    *                   type="string",
    *                   example="paytm"
    *               ),
    *               @OA\Property(
    *                   property="transaction_id",
    *                   type="string",
    *                   example="34hdfhjd378343yu3ui4"
    *               ),
    *           ),
    *       ),
    *       ),
    *      @OA\Response(
    *          response=200,
    *          description="Success"
    *       ),
    *       @OA\Response(response=400, description="Bad request"),
    *     )
    *
    * Returns list of Posts
    */
    public function enrollMasterClass(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'master_class_id' => 'required|numeric|exists:master_classes,id',
            'payment_gateway' => 'required',
            'transaction_id'  => 'required',
            'promoter_id'     => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }


        try {
            if ($request->filled('master_class_id')) {
                $bookingData = MasterClass::where('id', $request->master_class_id)->status()->first();
                if ($bookingData) {
                    if ($bookingData->already_purchased == false) {
                        if ($request->filled('promoter_id')) {
                            $promoterUser = User::where('iUserId', $request->promoter_id)->status()->first();

                            if (!$promoterUser) {
                                $response['status'] = false;
                                $response['message'] = trans('messages.promoter_not_found');
                                return response($response);
                            }
                        }

                        if (!empty(auth()->user()->vEmail) && !empty($bookingData->refresh_token) && filter_var(auth()->user()->vEmail, FILTER_VALIDATE_EMAIL)) {
                            // WebexService::addAttendee($bookingData->refresh_token, $bookingData, auth()->user());
                        }

                        $masterClassUser = MasterClassUser::where('user_id', auth()->user()->iUserId)
                            ->where('master_class_id', $bookingData->id)
                            ->where('promoter_id', $request->promoter_id)
                            ->first();

                        if (!$masterClassUser) {
                            $masterClassUserData = new MasterClassUser();
                            $masterClassUserData->user_id = auth()->user()->iUserId;
                            $masterClassUserData->master_class_id = $bookingData->id;
                            if ($request->filled('promoter_id')) {
                                $masterClassUserData->promoter_id = $request->promoter_id;
                            }
                            $masterClassUserData->save();
                        }
                        $paymentTransaction = new PaymentTransaction();
                        $paymentTransaction->user_id = auth()->user()->iUserId;
                        $paymentTransaction->payment_gateway = $request->payment_gateway;
                        $paymentTransaction->transaction_id = $request->transaction_id;
                        $paymentTransaction->master_class_id = isset($request->master_class_id) ? $request->master_class_id : NULL;
                        $paymentTransaction->call_booking_id = NULL;
                        $paymentTransaction->sub_total = $bookingData->amount;
                        $paymentTransaction->tax = 0;
                        $paymentTransaction->discount_amount = 0;
                        $paymentTransaction->discount_code = NULL;
                        $paymentTransaction->total = $bookingData->amount;
                        if ($request->filled('promoter_id')) {
                            $paymentTransaction->affiliate_user_id = $request->promoter_id;
                        }
                        $paymentTransaction->save();
                    } else {
                        $response['status'] = false;
                        $response['message'] = trans('messages.already_purchased');
                        return response($response);
                    }

                } else {
                    $response['status'] = false;
                    $response['message'] = trans('messages.no_data_found');
                    return response($response);
                }
            }
        } catch (Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
        }
        return response($response);
    }

    /**
     * Get Master Class List by Category
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 07/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/master-class-list-by-category",
     *      operationId="getMasterClassListByCategory",
     *      tags={"Master Class"},
     *      summary="Get Master Class List By Category",
     *      description="Returns list of Master Class by category",
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
     *      @OA\Parameter(
     *          name="category_id",
     *          description="Category Id (optional) - default all",
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
     * Returns list of Posts
     */
    public function getMasterClassListByCategory(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $today = Carbon::now()->setTimezone('Asia/Kolkata');

        if(auth('sanctum')->check()){
            $userId = auth('sanctum')->user()->iUserId;
        }else{
            $userId = NULL;
        }
        $masterClassData = MasterCategories::status()
        ->with(['masterClass' => function($query) use($userId, $today) {
            if(auth('sanctum')->check()){
                $query->where('user_id', '!=', $userId);
            }
            $query->having(DB::raw('CONCAT(start_date," ", start_time)'), '>=', $today);
            $query->status();
            $query->whereDoesntHave('userMasterClass', function ($userQuery) use ($userId){
                return $userQuery->where('master_class_users.user_id', $userId);
            });
        },'masterClass.user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id','masterClass.user.country','masterClass.user.state', 'masterClass.user.district', 'masterClass.user.taluka'])
        ->whereHas('masterClass', function($query) use($userId, $today) {
            if(auth('sanctum')->check()){
                $query->where('user_id', '!=', $userId);
            }
            $query->having(DB::raw('CONCAT(start_date," ", start_time)'), '>=', $today);
            $query->status();
            $query->whereDoesntHave('userMasterClass', function ($userQuery) use ($userId){
                return $userQuery->where('master_class_users.user_id', $userId);
            });
        });

        if($request->filled('category_id')){
            $masterClassData = $masterClassData->where('id', $request->category_id);
        }

        $masterClassData = $masterClassData->simplePaginate(intval($request->per_page));

        $response['data'] = $masterClassData ?? [];

        return response($response);
    }

    /**
    * Promote Master Class
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 08/07/2022
    */
    /**
    * @OA\Post(
    *       path="/api/v1/promote-master-class",
    *       operationId="promoteMasterClass",
    *       tags={"Master Class"},
    *       summary="Promote Master Class",
    *       description="Promote Master Class",
    *       security={ {"bearerAuth": {} }},
    *       @OA\RequestBody(
    *       @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(
    *               required={"master_class_id"},
    *               @OA\Property(
    *                   property="master_class_id",
    *                   type="integer",
    *                   example="1"
    *               ),
    *           ),
    *       ),
    *       ),
    *      @OA\Response(
    *          response=200,
    *          description="Success"
    *       ),
    *       @OA\Response(response=400, description="Bad request"),
    *     )
    *
    * Returns list of Posts
    */
    public function promoteMasterClass(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'master_class_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        if($request->filled('master_class_id')){
            $promoteData = MasterClass::where('id', $request->master_class_id)->first();

            if ($promoteData) {

                if($promoteData->user_id == auth()->user()->iUserId){
                    $response['status'] = false;
                    $response['message'] = trans('messages.can_not_promote');
                    return response($response);
                }else{
                    $promoteData->promoteMasterClass()->syncWithoutDetaching(auth()->user()->iUserId);
                }

            }else{
                $response['status'] = false;
                $response['message'] = trans('messages.no_data_found');
                return response($response);
            }
        }

        return response($response);
    }

    /**
     * Get Master Class Detail
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 08/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/master-class-detail",
     *      operationId="getMasterClassDetail",
     *      tags={"Master Class"},
     *      summary="Get Master Class Detail",
     *      description="Returns data of Master Class Detail",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="master_class_id",
     *          description="Master Class Id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
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
     * Returns list of Posts
     */
    public function getMasterClassDetail(Request $request)
    {
        $response = ['status' => true, 'data' => (object)[], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'master_class_id' => 'required|numeric',
            'filter_by'       => 'in:today,yesterday,7-day,this-month,last-month,all'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $masterClassData = MasterClass::select(['id', 'user_id', 'title', 'banner_image', 'start_date', 'start_time', 'amount','is_master_class_started', 'is_master_class_ended', 'meeting_link', 'duration', 'duration'])
        ->where('id', $request->master_class_id)
        ->status()
        ->first();

        if(!$masterClassData){
            $response['status'] = false;
            $response['message'] = trans('messages.not_active');
            return response($response);
        }

        if($masterClassData->user_id == auth()->user()->iUserId){

            $masterClassData->load(['user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id',
            'user.country','user.state', 'user.district', 'user.taluka',
            'categories:id,category_name',
            'userMasterClass' => function($q) use($request){
                filterByDates($q, $request);
            },'userMasterClass.country','userMasterClass.state', 'userMasterClass.district', 'userMasterClass.taluka',
            'myAffilitorUsers' => function($q) use($request){
                filterByDates($q, $request);
            },'myAffilitorUsers.country','myAffilitorUsers.state', 'myAffilitorUsers.district', 'myAffilitorUsers.taluka']);
        }else{

            $masterClassData->load(['user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id',
            'user.country','user.state', 'user.district', 'user.taluka',
            'categories:id,category_name',
            'promoterMyUsers' => function($q) use($request){
                filterByDates($q, $request);
            },'promoterMyUsers.country','promoterMyUsers.state', 'promoterMyUsers.district', 'promoterMyUsers.taluka',
            'promoterMyAffilitorUsers' => function($q) use($request){
                filterByDates($q, $request);
            },'promoterMyAffilitorUsers.country','promoterMyAffilitorUsers.state', 'promoterMyAffilitorUsers.district', 'promoterMyAffilitorUsers.taluka']);

            $masterClassData->user_master_class = $masterClassData->promoterMyUsers;
            $masterClassData->my_affilitor_users = $masterClassData->promoterMyAffilitorUsers;
            $masterClassData->unsetRelation('promoterMyUsers');
            $masterClassData->unsetRelation('promoterMyAffilitorUsers');
        }


        $response['data'] = $masterClassData ?? [];

        return response($response);
    }

    /**
     * Get Master Class Bookings
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 14/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/get-master-class-bookings",
     *      operationId="getMasterClassBookings",
     *      tags={"Master Class"},
     *      summary="Get Master Class Bookings",
     *      description="Returns list of Master Class Bookings",
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
     *      @OA\Parameter(
     *          name="screen",
     *          description="Screen Name",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="user",
     *              enum={"user", "creator"}
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="filter_by_status",
     *          description="Filter data based on booking status",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="booked",
     *              enum={"booked", "attended", "missed"}
     *          )
     *      ),
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
     * Returns list of Posts
     */
    public function getMasterClassBookings(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'screen'           => 'required|in:user,creator',
            'filter_by_status' => 'required|in:booked,attended,missed',
            'filter_by'        => 'nullable|in:today,yesterday,7-day,this-month,last-month,all'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $masterClassBookings = User::select(['iUserId','vFirstName','vLastName','vEmail','vImage','vOccupation','vPhoneNumber','country_id','state_id','district_id','taluka_id'])
        ->with(['country','state','district','taluka']);

        if($request->screen == 'user'){

            $masterClassBookings = $masterClassBookings->with(['masterClassPurchased'])->whereHas('masterClassPurchased', function($query) use($request){
                $query->where('master_class_users.status', $request->filter_by_status);
                $query->where('master_class_users.user_id', auth()->user()->iUserId);
            })
            ->where('iUserId', auth()->user()->iUserId);

        } else if($request->screen == 'creator'){

            $masterClassBookings = $masterClassBookings->with(['masterClassPurchased'])->whereHas('masterClassPurchased', function($query) use($request){
                $query->where('master_class_users.status', $request->filter_by_status);
                $query->where('master_classes.user_id', auth()->user()->iUserId);
            });
        }


        $masterClassBookings = $masterClassBookings->status()
        ->filterBooking($request)
        ->simplePaginate(intval($request->per_page));

        if(!$masterClassBookings){
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return response($response);
        }

        $response['data'] = $masterClassBookings ?? [];

        return response($response);
    }


    /**
     * Get Affiliate
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 26/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/get-affiliate",
     *      operationId="getAffiliate",
     *      tags={"Master Class"},
     *      summary="Get Affiliates detail for user",
     *      description="Returns data of affiliates",
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
     *      @OA\Parameter(
     *          name="tab_name",
     *          description="Tab Name",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="promoted-by-me",
     *              enum={"promoted-by-me", "my-promoter"}
     *          )
     *      ),
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
     * Returns list of Posts
     */
    public function getAffiliate(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'tab_name'  => 'required|in:promoted-by-me,my-promoter',
            'filter_by' => 'in:today,yesterday,7-day,this-month,last-month,all'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $masterClassData = MasterClass::select(['id', 'user_id', 'title', 'banner_image', 'start_date', 'start_time', 'amount', 'is_master_class_started', 'is_master_class_ended','meeting_link'])
            ->with(['user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id',
            'user.country','user.state', 'user.district', 'user.taluka',
            'categories:id,category_name']);

        if($request->tab_name == 'promoted-by-me'){

            $promotedMasterClassData = MasterClassPromoter::where('user_id', auth()->user()->iUserId)->get()->pluck('master_class_id')->toArray();

            $masterClassData = $masterClassData->whereIn('id', $promotedMasterClassData)
            ->status()
            ->filterPromoted($request)
            ->simplePaginate(intval($request->per_page));
        }else if($request->tab_name == 'my-promoter'){

            $masterClassData = $masterClassData->where('user_id', auth()->user()->iUserId)
            ->with('promoteMasterClass')
            ->status()
            ->filterPromoted($request)
            ->simplePaginate(intval($request->per_page));
        }

        if(!$masterClassData){
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return response($response);
        }

        $response['data'] = $masterClassData ?? [];

        return response($response);
    }

    /**
    * Update Meeting Link
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 13/09/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/update-meeting-link",
    *      operationId="updateMeetingLink",
    *      tags={"Master Class", "Call Booking"},
    *      summary="Update Meeting Link",
    *      description="Update Meeting Link",
    *      security={ {"bearerAuth": {} }},
    *      @OA\Parameter(
    *          name="id",
    *          description="Master Class or Call Booking ID",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="integer"
    *          )
    *      ),
    *      @OA\Parameter(
    *          name="meeting_link",
    *          description="Meeting Link for Call",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *      @OA\Parameter(
    *          name="module_name",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="string",
    *              default="master_class",
    *              enum={"master_class", "call_booking"}
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
    * ),
    * @OA\Response(response=400, description="Bad request"),
    * )
    */
    public function updateMeetingLink(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'id'           => 'required|numeric',
            'meeting_link' => 'required',
            'module_name'  => 'required|in:master_class,call_booking'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        if($request->module_name == 'master_class'){
            $moduleData = MasterClass::where('id', $request->id)
            ->where('user_id', auth()->user()->iUserId)
            ->first();
        }else if($request->module_name == 'call_booking'){
            $moduleData = CallBooking::where('id', $request->id)
            ->where('creator_id', auth()->user()->iUserId)
            ->first();
        }

        if (!$moduleData) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return response($response);
        }

        $inputData = $request->all();

        $moduleData->update($inputData);

        $response['data'] = $moduleData->refresh();

        return response($response);
    }

    /**
    * Update Master Class Started Flag
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 10/10/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/update-mastar-class-start",
    *      operationId="updateMasterClassStart",
    *      tags={"Master Class"},
    *      summary="Update Master Class Started Flag",
    *      description="Update Master Class Started Flag",
    *      security={ {"bearerAuth": {} }},
    *      @OA\Parameter(
    *          name="id",
    *          description="Master Class ID",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="integer"
    *          )
    *      ),
    *      @OA\Parameter(
    *          name="is_started",
    *          description="Is Master Class Started",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="integer",
    *              default="1",
    *              enum={"1", "0"}
    *          )
    *      ),
    *
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
    public function updateMasterClassStart(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'id'         => 'required|numeric',
            'is_started' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }


        $masterClassData = MasterClass::where('id', $request->id)
        ->where('user_id', auth()->user()->iUserId)
        ->status()
        ->first();


        if (!$masterClassData) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return response($response);
        }

        //for end master class
        if($request->is_started == "0" && $masterClassData->is_master_class_started == "1"){
            $masterClassData->is_master_class_ended = "1";
        }

        $masterClassData->is_master_class_started = $request->is_started;

        $masterClassData->update();

        if($request->is_started == "1"){
            //Send notification to all users
            Notification::send($masterClassData->bookingUsers, new BeforeMasterClass("Your Master class - ".$masterClassData->title." has started.", $masterClassData));
        }


        $response['data'] = $masterClassData->refresh();

        return response($response);
    }
}
