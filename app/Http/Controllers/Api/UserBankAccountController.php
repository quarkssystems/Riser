<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\UserBankAccount;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserBankAccountController extends Controller
{
    /**
    * Add Bank Account
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 26/07/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/add-bank-account",
    *      operationId="addBankAccount",
    *      tags={"User"},
    *      summary="Add Bank Account",
    *      description="Add Bank Account API",
    *      security={ {"bearerAuth": {} }},
    *      @OA\RequestBody(
    *      @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(
    *               required={"full_name", "account_number", "ifsc_code", "bank_name", "branch_name"},
    *               @OA\Property(
    *                   property="full_name",
    *                   description="Full name of user appear on bank account",
    *                   example="John Smith",
    *                   type="string",
    *               ),
    *               @OA\Property(
    *                   property="account_number",
    *                   description="Account number for bank",
    *                   example="4545677BB787",
    *                   type="string",
    *               ),
    *               @OA\Property(
    *                   property="ifsc_code", 
    *                   description="IFSC number for bank",
    *                   example="ICI232322",
    *                   type="string", 
    *               ),
    *               @OA\Property(
    *                   property="bank_name",
    *                   description="Bank name",
    *                   example="SBI",
    *                   type="string",
    *               ),
    *               @OA\Property(
    *                   property="branch_name",
    *                   description="Branch name of bank",
    *                   example="C.G. Road Branch",
    *                   type="string",
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
    public function addBankAccount(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        
        $validator = Validator::make($request->all(), [
            'full_name'      => 'required|string',
            'account_number' => 'required|string',
            'ifsc_code'      => 'required|string',
            'bank_name'      => 'required|string',
            'branch_name'    => 'required|string',
        ]);
        
        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();
            
            return response($response, 200);
        }
        
        $userBankAccountData['user_id'] = auth()->user()->iUserId;
        $userBankAccountData['full_name'] = $request->full_name;
        $userBankAccountData['account_number'] = $request->account_number;
        $userBankAccountData['ifsc_code'] = $request->ifsc_code;
        $userBankAccountData['bank_name'] = $request->bank_name;
        $userBankAccountData['branch_name'] = $request->branch_name;
        
        $bankAccount = UserBankAccount::updateOrCreate($userBankAccountData, $userBankAccountData);
        
        if($bankAccount){
            $response['data'] = $bankAccount;
        }else{
            $response['status'] = false;
        }

        return response($response);
    }

    /**
     * Get Accounts
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 26/07/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/get-bank-accounts",
     *      operationId="getBankAccounts",
     *      tags={"User"},
     *      summary="Get Bank Accounts",
     *      description="Get Bank Accounts",
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
    public function getBankAccounts(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        
        $userBankAccountData = UserBankAccount::status()
        ->where('user_id', auth()->user()->iUserId)
        ->simplePaginate(intval($request->per_page));

        if ($userBankAccountData->count() == 0) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
        }

        $response['data'] = $userBankAccountData;

        return response($response);
    }

    /**
    * Edit Bank Account
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 26/07/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/edit-bank-account",
    *      operationId="editBankAccount",
    *      tags={"User"},
    *      summary="Edit Bank Account",
    *      description="Edit Bank Account API",
    *      security={ {"bearerAuth": {} }},
    *      @OA\RequestBody(
    *      @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(
    *               required={"full_name", "account_number", "ifsc_code", "bank_name", "branch_name"},
    *               @OA\Property(
    *                   property="id",
    *                   description="Bank account id primary key",
    *                   example="1",
    *                   type="integer",
    *               ),
    *               @OA\Property(
    *                   property="full_name",
    *                   description="Full name of user appear on bank account",
    *                   example="John Smith",
    *                   type="string",
    *               ),
    *               @OA\Property(
    *                   property="account_number",
    *                   description="Account number for bank",
    *                   example="4545677BB787",
    *                   type="string",
    *               ),
    *               @OA\Property(
    *                   property="ifsc_code", 
    *                   description="IFSC number for bank",
    *                   example="ICI232322",
    *                   type="string", 
    *               ),
    *               @OA\Property(
    *                   property="bank_name",
    *                   description="Bank name",
    *                   example="SBI",
    *                   type="string",
    *               ),
    *               @OA\Property(
    *                   property="branch_name",
    *                   description="Branch name of bank",
    *                   example="C.G. Road Branch",
    *                   type="string",
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
    public function editBankAccount(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        
        $validator = Validator::make($request->all(), [
            'id'             => 'required|numeric',
            'full_name'      => 'required|string',
            'account_number' => 'required|string',
            'ifsc_code'      => 'required|string',
            'bank_name'      => 'required|string',
            'branch_name'    => 'required|string',
        ]);
        
        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();
            
            return response($response, 200);
        }
        
        $bankAccount = UserBankAccount::where('id', $request->id)
        ->where('user_id', auth()->user()->iUserId)
        ->first();
        
        if (!$bankAccount) {
            $response['status'] = false;
            $response['message'] = trans('messages.not_authorized');
            return response($response);
        }

        $userBankAccountData['full_name'] = $request->full_name;
        $userBankAccountData['account_number'] = $request->account_number;
        $userBankAccountData['ifsc_code'] = $request->ifsc_code;
        $userBankAccountData['bank_name'] = $request->bank_name;
        $userBankAccountData['branch_name'] = $request->branch_name;
        
        $bankAccount->update($userBankAccountData);

        $response['data'] = $bankAccount->refresh();

        return response($response);
    }

    /**
    * Delete Bank Account
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 26/07/2022
    */
    /**
    * @OA\Delete(
    *       path="/api/v1/delete-bank-account",
    *       operationId="deleteBankAccount",
    *       tags={"User"},
    *       summary="Delete Bank Account",
    *       description="Delete Bank Account",
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
    public function deleteBankAccount(Request $request)
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

        $userBankAccountData = UserBankAccount::where('id', $request->id)
        ->where('user_id', auth()->user()->iUserId)
        ->delete();
        
        if (!$userBankAccountData) {
            $response['status'] = false;
            $response['message'] = trans('messages.not_authorized');
            return response($response);
        }

        return response($response);
    }
}
