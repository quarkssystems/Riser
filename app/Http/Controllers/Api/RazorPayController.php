<?php

namespace App\Http\Controllers\Api;

use Razorpay\Api\Api;
use App\Models\CallBooking;
use App\Models\MasterClass;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RazorPayController extends Controller
{
    /**
    * Creator Order on razorpay
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 30/09/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/razorpay-create-order",
    *      operationId="razorPayCreateOrder",
    *      tags={"Payment"},
    *      summary="Create order on razorpay using API",
    *      description="Create order on razorpay using API",
    *      security={ {"bearerAuth": {} }},
    *      @OA\RequestBody(
    *      @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(
    *               required={"user_id", "module_name", "module_id"},
    *               @OA\Property(
    *                   property="user_id", 
    *                   type="number", 
    *                   format="integer", 
    *                   example="1"
    *               ),
    *               @OA\Property(
    *                   property="module_name", 
    *                   type="string", 
    *                   default="master_class",
    *                   enum={"master_class", "call_booking"}
    *               ),
    *               @OA\Property(
    *                   property="module_id", 
    *                   type="number", 
    *                   format="integer", 
    *                   example="1"
    *               ),
    *               @OA\Property(
    *                   property="amount", 
    *                   type="number", 
    *                   format="integer", 
    *                   example="1"
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
    public function razorPayCreateOrder(Request $request)
    {
        $response = ['status' => true, 'data' => (object)[], 'errors' => []];

        $moduleData = NULL;
        $amount = 0;
        $receiptPrefix = "";
        
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|integer|exists:tbl_users,iUserId',
            'module_name' => 'required|in:master_class,call_booking,wallet',
            'module_id'   => 'required|numeric|min:0',
            'amount'      => 'required_if:module_name,wallet|numeric|min:1',
        ]);
        
        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();
            
            return response($response, 200);
        }

        try {

            if($request->module_name == 'master_class'){     
                $moduleData = MasterClass::where('id', $request->module_id)->first();
                $amount = $moduleData ? $moduleData->amount : $amount;
                $receiptPrefix = "RMC-";
            }else if($request->module_name == 'call_booking'){
                $moduleData = CallBooking::where('id', $request->module_id)->first();
                $amount = $moduleData ? $moduleData->booking_amount : $amount;
                $receiptPrefix = "RCB-";
            }else if($request->module_name == 'wallet'){
                $amount = $request->amount ? $request->amount : $amount;
                $receiptPrefix = "RWA-";
            }

            if($amount < 1){
                $response['status'] = false;
                $response['message'] = trans('messages.minimum_amount', ['amount' => $amount]);
                return response($response);
            }
    
            if($moduleData || $request->module_name == 'wallet'){
                $api = new Api(config('constant.razorpay_key'), config('constant.razorpay_secret'));
                $order = $api->order->create(
                    array(
                        'receipt' => $receiptPrefix.bin2hex(random_bytes(5)), 
                        'amount' => $amount*100, 
                        'currency' => 'INR', 
                        'notes'=> array(
                            'module_name'=> $request->module_name,
                            'module_id'  => $request->module_id,
                        )
                    )
                );
                $response['data'] = $order->toArray();

            }else{
                $response['status'] = false;
                $response['message'] = trans('messages.no_data_found');
            }

        } catch (Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
            return response($response);
        }

        return response($response);
    }
}
