<?php

namespace App\Http\Controllers\Api;

use App\Services\WebexService;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\CallBooking;
use App\Models\CallPackage;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Models\PaymentTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\CallBookingRequest;
use Illuminate\Support\Facades\Validator;

class CallBookingController extends Controller
{
    /**
     * Get Call Booking Packages
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 20/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/call-booking-packages",
     *      operationId="getCallBookingPackages",
     *      tags={"Call Booking"},
     *      summary="Get Call Booking Packages List",
     *      description="Returns list of Call Booking Packages",
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
     * Returns list of Posts
     */
    public function getCallBookingPackages(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $callBookingPackagesData = CallPackage::orderBy('id', 'ASC')
            ->status()
            ->simplePaginate(intval($request->per_page));

        $response['data'] = $callBookingPackagesData ?? [];

        return response($response);
    }

    /**
     * Call Booking Request
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 20/07/2022
     */
    /**
     * @OA\Post(
     *      path="/api/v1/call-booking-request",
     *      operationId="callBookingRequest",
     *      tags={"Call Booking"},
     *      summary="Call Booking Request",
     *      description="Call Booking Request API",
     *      security={ {"bearerAuth": {} }},
     *      @OA\RequestBody(
     *      @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *               required={"creator_id", "call_package_id", "booking_date", "start_time"},
     *               @OA\Property(
     *                   property="creator_id",
     *                   description="Id of creator whom call is booking",
     *                   example="1",
     *                   type="integer"
     *               ),
     *               @OA\Property(
     *                   property="call_package_id",
     *                   description="Call Package Id",
     *                   example="1",
     *                   type="integer"
     *               ),
     *               @OA\Property(
     *                   property="booking_date",
     *                   type="string",
     *                   format="date",
     *                   example="2022-07-20"
     *               ),
     *               @OA\Property(
     *                   property="start_time",
     *                   type="string",
     *                   example="01:30 PM"
     *               ),
     *               @OA\Property(
     *                   property="booking_message",
     *                   type="string",
     *                   example="Message about the booking purpose"
     *               ),
     *           ),
     *       ),
     *   ),
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
    public function callBookingRequest(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'creator_id' => 'required|integer|exists:tbl_users,iUserId',
            'call_package_id' => 'required|integer',
            'booking_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'required|date_format:h:i A',
            'booking_message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        //validation for past date & time
        $todayDateTime = Carbon::now('Asia/Kolkata')->format('Y-m-d h:i A');
        $bookingDate = $request->booking_date;
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

        $callBookingPackage = CallPackage::where('id', $request->call_package_id)->status()->first();

        if ($callBookingPackage) {
            $endTime = Carbon::parse($request->start_time)->addMinutes($callBookingPackage->duration_minutes)->format('H:i');

            $existingCallBooking = CallBooking::where(function ($query) use ($request, $endTime) {
                $query
                    ->where(function ($query) use ($request, $endTime) {
                        $query->where('start_time', '>=', $request->start_time);
                        $query->where('start_time', '<', $endTime);
                    })
                    ->orWhere(function ($query) use ($request, $endTime) {
                        $query->where('start_time', '<=', $request->start_time);
                        $query->where('end_time', '>', $endTime);
                    })
                    ->orWhere(function ($query) use ($request, $endTime) {
                        $query->where('end_time', '>', $request->start_time);
                        $query->where('end_time', '<=', $endTime);
                    })
                    ->orWhere(function ($query) use ($request, $endTime) {
                        $query->where('start_time', '>=', $request->start_time);
                        $query->where('end_time', '<=', $endTime);
                    });
            })
                ->where('creator_id', $request->creator_id)
                ->where('booking_date', $request->booking_date)
                ->where('status', '!=', config('constant.status.rejected_value'))
                ->count();

            if ($existingCallBooking > 0) {
                $response['status'] = false;
                $response['message'] = trans('messages.slot_not_available');
                return response($response);
            }

            $callBookingData = new CallBooking();
            $callBookingData->user_id = auth()->user()->iUserId;
            $callBookingData->creator_id = $request->creator_id;
            $callBookingData->call_package_id = $request->call_package_id;
            $callBookingData->booking_date = $request->booking_date;
            $callBookingData->start_time = $request->start_time;
            $callBookingData->end_time = $endTime;
            $callBookingData->booking_amount = $callBookingPackage->price;
            $callBookingData->booking_message = $request->booking_message;

            $creatorUser = User::where('iUserId', $request->creator_id)->first();

            if ($callBookingData->save()) {
                $creatorUser->notify(new CallBookingRequest(auth()->user()->full_name . " has requested for a call", $callBookingData, auth()->user()));
            }
        } else {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return response($response);
        }

        $response['data'] = $callBookingData;

        return response($response);
    }

    /**
     * Get Call Booking List
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 21/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/call-booking-list",
     *      operationId="getCallBookingList",
     *      tags={"Call Booking"},
     *      summary="Get Call Booking List",
     *      description="Returns list of Call Booking",
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
     *              default="requested",
     *              enum={"requested", "booked", "attended", "missed"}
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
    public function getCallBookingList(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'screen' => 'required|in:user,creator',
            'filter_by_status' => 'required|in:requested,booked,attended,missed',
            'filter_by' => 'nullable|in:today,yesterday,7-day,this-month,last-month,all'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $userId = auth()->user()->iUserId;

        $callBookingData;
        $today = Carbon::now()->format('Y-m-d');
        if ($request->screen == 'user') {

            $callBookingData = CallBooking::
            with(['creator:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id', 'creator.country', 'creator.state', 'creator.district', 'creator.taluka'])
                ->where('user_id', $userId);

        } else if ($request->screen == 'creator') {

            $callBookingData = CallBooking::
            with(['user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id', 'user.country', 'user.state', 'user.district', 'user.taluka'])
                ->where('creator_id', $userId);
        }

        if ($request->filter_by_status == 'requested') {
            $callBookingData = $callBookingData->whereIn('status', ['requested', 'approved', 'rejected']);
            $callBookingData = $callBookingData->whereRaw("CONCAT(booking_date,' ', start_time) >= '" . $today . "'");
        } else {
            $callBookingData = $callBookingData->where('status', $request->filter_by_status);
        }
        $callBookingData = $callBookingData->orderBy('booking_date', 'desc')
            ->orderBy('id', 'desc')
            ->filter($request)
            ->simplePaginate(intval($request->per_page));

        $response['data'] = $callBookingData ?? [];

        return response($response);
    }

    /**
     * Accept/Reject Call
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 21/07/2022
     */
    /**
     * @OA\Post(
     *       path="/api/v1/accept-reject-call",
     *       operationId="acceptRejectCall",
     *       tags={"Call Booking"},
     *       summary="Accept or Reject Call",
     *       description="Accept or Reject Call",
     *       security={ {"bearerAuth": {} }},
     *       @OA\Parameter(
     *           name="call_booking_id",
     *           description="Call Booking Id",
     *           required=true,
     *           in="query",
     *           @OA\Schema(
     *               type="integer"
     *           )
     *       ),
     *       @OA\Parameter(
     *           name="action",
     *           description="Accept or Reject",
     *           required=true,
     *           in="query",
     *           @OA\Schema(
     *               type="string",
     *               default="accept",
     *               enum={"accept", "reject"}
     *           )
     *       ),
     *       @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of Posts
     */
    public function acceptRejectCall(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'call_booking_id' => 'required|numeric',
            'action' => 'required|in:accept,reject',
            // 'webex_auth_code' => 'required_if:action,accept|string',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }
        try {
            if ($request->filled('call_booking_id')) {
                $callBookingData = CallBooking::where('id', $request->call_booking_id)
                    ->status(config('constant.status.requested_value'))
                    ->first();

                if ($callBookingData) {

                    if ($callBookingData->creator_id != auth()->user()->iUserId) {
                        $response['status'] = false;
                        $response['message'] = trans('messages.not_authorized');
                        return response($response);
                    } else {
                        $bookingStatus['accept'] = config('constant.status.approved_value');
                        $bookingStatus['reject'] = config('constant.status.rejected_value');
                        $callBookingData->status = $bookingStatus[$request->action];
                        $user = User::where('iUserId', $callBookingData->user_id)->first();

                        // if ($request->action === "accept") {
                        //     $tokens = WebexService::getAccessToken('authorization_code', 'code', $request->get('webex_auth_code'), true);
                        //     $callBookingData->refresh_token = $tokens['refresh_token'];
                        // }

                        if ($callBookingData->save()) {
                            $user->notify(new CallBookingRequest(auth()->user()->full_name . " has " . $bookingStatus[$request->action] . " your call request", $callBookingData, auth()->user()));
                        }
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
     * Book call after payment
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 21/07/2022
     */
    /**
     * @OA\Post(
     *       path="/api/v1/book-call-after-payment",
     *       operationId="bookCallAfterPayment",
     *       tags={"Call Booking"},
     *       summary="Book call after payment",
     *       description="Book call after payment",
     *       security={ {"bearerAuth": {} }},
     *       @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *               required={"call_booking_id","payment_gateway","transaction_id"},
     *               @OA\Property(
     *                   property="call_booking_id",
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
    public function bookCallAfterPayment(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'call_booking_id' => 'required|numeric',
            'payment_gateway' => 'required',
            'transaction_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        try {
            if ($request->filled('call_booking_id')) {
                $bookingData = CallBooking::where('id', $request->call_booking_id)
                    ->status(config('constant.status.approved_value'))
                    ->first();

                if ($bookingData) {
                    if (!empty(auth()->user()->vEmail) && !empty($bookingData->refresh_token) && filter_var(auth()->user()->vEmail, FILTER_VALIDATE_EMAIL)) {
                        $data['start_date_time'] = Carbon::parse($bookingData['booking_date'])->setTimeFromTimeString(Carbon::parse($bookingData->start_time)->format('H:i'));
                        $data['start_date_time'] = Carbon::createFromFormat('Y-m-d H:i:s', $data['start_date_time']->toDateTimeString(), 'Asia/Kolkata')->setTimezone('UTC');
                        $data['end_date_time'] = Carbon::parse($bookingData['booking_date'])->setTimeFromTimeString(Carbon::parse($bookingData->end_time)->format('H:i'));
                        $data['end_date_time'] = Carbon::createFromFormat('Y-m-d H:i:s', $data['end_date_time']->toDateTimeString(), 'Asia/Kolkata')->setTimezone('UTC');
                        $data['title'] = 'Call booking - Request';
                        // $meetingDetail = WebexService::createMeetingWithAttendee($bookingData->refresh_token, $data, auth()->user());
                        // $bookingData->refresh_token = $meetingDetail['refresh_token'] ?? null;
                        // $bookingData->meeting_link = $meetingDetail['meeting']->webLink;
                        // $bookingData->meeting_id = $meetingDetail['meeting']->id;
                    }
                    $bookingData->status = config('constant.status.booked_value');
                    $bookingData->save();

                    $paymentTransaction = new PaymentTransaction();
                    $paymentTransaction->user_id = auth()->user()->iUserId;
                    $paymentTransaction->payment_gateway = $request->payment_gateway;
                    $paymentTransaction->transaction_id = $request->transaction_id;
                    $paymentTransaction->master_class_id = NULL;
                    $paymentTransaction->call_booking_id = $bookingData->id;
                    $paymentTransaction->sub_total = $bookingData->booking_amount;
                    $paymentTransaction->tax = 0;
                    $paymentTransaction->discount_amount = 0;
                    $paymentTransaction->discount_code = NULL;
                    $paymentTransaction->total = $bookingData->booking_amount;
                    $paymentTransaction->save();

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
     * Join Call
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 21/07/2022
     */
    /**
     * @OA\Post(
     *       path="/api/v1/join-call",
     *       operationId="joinCall",
     *       tags={"Call Booking"},
     *       summary="Join Call",
     *       description="Join Call",
     *       security={ {"bearerAuth": {} }},
     *       @OA\Parameter(
     *           name="call_booking_id",
     *           description="Call Booking Id",
     *           required=true,
     *           in="query",
     *           @OA\Schema(
     *               type="integer"
     *           )
     *       ),
     *       @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of Posts
     */
    public function joinCall(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'call_booking_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        if ($request->filled('call_booking_id')) {
            $today = Carbon::now()->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s');

            $callBookingData = CallBooking::where('id', $request->call_booking_id)
                ->status(config('constant.status.booked_value'))
                ->first();

            if ($callBookingData) {
                $bookingDate = $callBookingData->booking_date . " " . $callBookingData->getRawOriginal('start_time');

                if ($bookingDate > $today) {
                    $response['status'] = false;
                    $response['message'] = trans('messages.call_before_time');
                    return response($response);
                }

                if ($callBookingData->user_id != auth()->user()->iUserId) {
                    $response['status'] = false;
                    $response['message'] = trans('messages.not_authorized');
                    return response($response);
                } else {
                    $callBookingData->status = config('constant.status.attended_value');
                    $callBookingData->save();
                }

            } else {
                $response['status'] = false;
                $response['message'] = trans('messages.no_data_found');
                return response($response);
            }
        }

        return response($response);
    }
}
