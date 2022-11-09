<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PaymentPayout;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    /**
     * Get Wallet Balance
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 27/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/get-wallet-balance",
     *      operationId="getWalletBalance",
     *      tags={"Wallet"},
     *      summary="Get User Wallet Balance with transaction history",
     *      description="Get User Wallet Balance with transaction history",
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
    public function getWalletBalance(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $userData = Auth::user()->load('wallet');
        $userData->wallet->refreshBalance();
        
        $response['data']['wallet_balance'] = $userData->balanceFloat;
        $response['data']['transactions'] = tap($userData->wallet->transactions()->orderBy('id', 'desc')->simplePaginate(intval($request->per_page)),function($paginatedInstance){
            return $paginatedInstance->getCollection()->transform(function ($value) {
                $value['amount'] = number_format($value->amountFloat, 2);
                return $value;
            });
        });

        return response($response);
    }

    /**
    * Add Balance to Wallet
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 27/07/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/add-balance-to-wallet",
    *      operationId="addBalanceToWallet",
    *      tags={"Wallet"},
    *      summary="Add balance to wallet",
    *      description="Add balance to wallet API",
    *      security={ {"bearerAuth": {} }},
    *      @OA\RequestBody(
    *      @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(
    *               required={"amount"},
    *               @OA\Property(
    *                   property="amount", 
    *                   type="number", 
    *                   format="double", 
    *                   example="100"
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
    public function addBalanceToWallet(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);
        
        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();
            
            return response($response, 200);
        }

        try {
            $userData = Auth::user()->load('wallet');
            $userData->depositFloat($request->amount);
            $userData->wallet->refreshBalance();
            $response['data']['wallet_balance'] = $userData->balanceFloat;
        } catch (Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
            return response($response);
        }

        return response($response);
    }

    /**
    * Withdraw from Wallet
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 27/07/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/withdraw-from-wallet",
    *      operationId="withdrawFromWallet",
    *      tags={"Wallet"},
    *      summary="Withdraw from wallet",
    *      description="Withdraw from wallet API",
    *      security={ {"bearerAuth": {} }},
    *      @OA\RequestBody(
    *      @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(
    *               required={"amount"},
    *               @OA\Property(
    *                   property="amount", 
    *                   type="number", 
    *                   format="double", 
    *                   example="100"
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
    public function withdrawFromWallet(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);
        
        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();
            
            return response($response, 200);
        }

        $today = Carbon::now()->setTimezone('Asia/Kolkata');
        $firstDate = 16;
        $secondDate = $today->endOfMonth()->format('d');
        
        if($today->format('d') != $firstDate || $today->format('d') != $secondDate){
            $response['status'] = false;
            $response['message'] = trans('messages.withdraw_dates', ['firstDate' => $firstDate, 'secondDate' => $secondDate]);
            return response($response, 200);
        };
        
        try {
            $userData = Auth::user()->load('wallet');
            $userData->withdrawFloat($request->amount);
            $userData->wallet->refreshBalance();
            $response['data']['wallet_balance'] = $userData->balanceFloat;
        } catch (Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
            return response($response);
        }
        

        return response($response);
    }

    /**
    * Send Gift To Creator
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 27/07/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/send-gift-to-creator",
    *      operationId="sendGiftToCreator",
    *      tags={"Wallet"},
    *      summary="Send Gift Amount to Creator",
    *      description="Send Gift Amount to Creator API",
    *      security={ {"bearerAuth": {} }},
    *      @OA\RequestBody(
    *      @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(
    *               required={"creator_id", "amount"},
    *               @OA\Property(
    *                   property="creator_id", 
    *                   type="number", 
    *                   format="integer", 
    *                   example="1"
    *               ),
    *               @OA\Property(
    *                   property="amount", 
    *                   type="number", 
    *                   format="double", 
    *                   example="100"
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
    public function sendGiftToCreator(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        
        $validator = Validator::make($request->all(), [
            'creator_id' => 'required|integer|exists:tbl_users,iUserId',
            'amount'     => 'required|numeric|min:1',
        ]);
        
        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();
            
            return response($response, 200);
        }

        if($request->creator_id == auth()->user()->iUserId){
            $response['status'] = false;
            $response['message'] = trans('messages.gift_yourself');
            return response($response, 200);
        }
        
        $creatorData = User::where('iUserId', $request->creator_id)->with('wallet')->first();
        
        if($creatorData->hasRole(config('constant.roles.creator')) == false){
            $response['status'] = false;
            $response['message'] = trans('messages.user_not_creator', ['role' => config('constant.roles.creator')]);
            return response($response, 200);
        }

        try {
            $userData = Auth::user()->load('wallet');
            $userData->withdrawFloat($request->amount);
            $userData->wallet->refreshBalance();
            $response['data']['wallet_balance'] = $userData->balanceFloat;

            $creatorData->depositFloat($request->amount);
            $giftData = new Gift();
            $giftData->user_id = auth()->user()->iUserId;
            $giftData->creator_id = $creatorData->iUserId;
            $giftData->amount = $request->amount;
            $giftData->save();

        } catch (Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
            return response($response);
        }

        return response($response);
    }

    /**
     * Get Gift Transactions
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 27/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/gift-transactions",
     *      operationId="giftTransactions",
     *      tags={"Wallet"},
     *      summary="Gift transaction history with filters",
     *      description="Gift transaction history with filters",
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
    public function giftTransactions(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $userGiftData = Gift::where('creator_id', auth()->user()->iUserId)
        ->filter($request);

        $total = collect(['total_amount' => $userGiftData->sum('amount')]);
        $giftData = $userGiftData->simplePaginate(intval($request->per_page));
        
        if($giftData->count() == 0){
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
        }

        $response['data'] = $total->merge($giftData);

        return response($response);
    }

    /**
     * Get My Earning
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 02/09/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/my-earning",
     *      operationId="myEarning",
     *      tags={"Wallet"},
     *      summary="Get my earning history with filters",
     *      description="Get my earning history with filters",
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
    public function myEarning(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $myEarningData = PaymentPayout::select('id','user_id','module_name','payout_amount','created_at')
        ->where('user_id', auth()->user()->iUserId)
        ->orderByDesc('created_at')
        ->filter($request);

        $total = collect(['my_earning' => $myEarningData->sum('payout_amount')]);
        $earningData = $myEarningData->simplePaginate(intval($request->per_page));
        
        if($earningData->count() == 0){
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
        }

        $response['data'] = $total->merge($earningData);

        return response($response);
    }

    /**
     * Get My Team Earning
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 02/09/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/my-team-earning",
     *      operationId="myTeamEarning",
     *      tags={"Wallet"},
     *      summary="Get my team earning history with filters",
     *      description="Get my team earning history with filters",
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
    public function myTeamEarning(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $myEarningData = PaymentPayout::select('id', 'user_id', 'creator_id', DB::raw('SUM(payout_amount) as payout_amount'), DB::raw('SUM(parent_payout_amount) as my_commission'),'created_at')
        ->with('creator:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber')
        ->where('parent_user_id', auth()->user()->iUserId)
        ->where('role', config('constant.roles.creator'))
        ->orderByDesc('created_at')
        ->filter($request);

        $total = collect(['my_team_earning' => $myEarningData->sum('payout_amount')]);
        $earningData = $myEarningData
        ->groupBy('creator_id')
        ->simplePaginate(intval($request->per_page));
        
        if($earningData->count() == 0){
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
        }

        $response['data'] = $total->merge($earningData);

        return response($response);
    }
}
