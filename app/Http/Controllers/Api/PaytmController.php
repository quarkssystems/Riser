<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use paytm\paytmchecksum\PaytmChecksum;
use Illuminate\Support\Facades\Validator;

class PaytmController extends Controller
{
    /**
    * Create Checksum
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 06/07/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/create-checksum",
    *      operationId="createChecksum",
    *      tags={"Payment"},
    *      summary="Create Checksum",
    *      description="Create Checksum API",
    *      security={ {"bearerAuth": {} }},
    *      @OA\RequestBody(
    *      @OA\MediaType(
    *           mediaType="application/x-www-form-urlencoded",
    *           @OA\Schema(
    *               required={"master_class_id"},
    *               @OA\Property(
    *                   property="master_class_id", 
    *                   type="integer", 
    *                   example="1"
    *               ),
    *               @OA\Property(
    *                   property="order_id", 
    *                   type="string", 
    *                   example="RA00001"
    *               ),
    *               @OA\Property(
    *                   property="amount", 
    *                   type="integer", 
    *                   example="100"
    *               ),
    *           ),
    *       ),
    *       ),
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
    public function createChecksum(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        
        $validator = Validator::make($request->all(), [
            'master_class_id' => 'required|numeric',
            'order_id'        => 'required',
            'amount'          => 'required|numeric'
        ]);
        
        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();
            
            return response($response, 200);
        }

        $paytmParams = array();

        $paytmParams["body"] = array(
            "requestType"   => "Payment",
            "mid"           => config('constant.paytm_merchant_id'),
            "websiteName"   => config('constant.paytm_website'),
            "orderId"       => $request->order_id,
            "callbackUrl"   => config('constant.paytm_callback').$request->order_id,
            "txnAmount"     => array(
                "value"     => $request->amount,
                "currency"  => "INR",
            ),
            "userInfo"      => array(
                "custId"    => "CUST_".auth()->user()->iUserId,
            ),
        );

        $checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), config('constant.paytm_merchant_key'));

        // $verifySignature = PaytmChecksum::verifySignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), config('constant.paytm_merchant_key'), $checksum);
        
        $paytmParams["head"] = array(
            "signature"    => $checksum
        );
        
        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
        
        $url = config('constant.paytm_gateway_url')."?mid=".config('constant.paytm_merchant_id')."&orderId=".$request->order_id;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); 
        $curlResponse = curl_exec($ch);
        
        if($curlResponse){
            $response['data'] = json_decode($curlResponse);
        }else{
            $response['status'] = false;
        }

        return response($response);
    }

    /**
    * Transaction Status API
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 05/08/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/transaction-status",
    *      operationId="transactionStatus",
    *      tags={"Payment"},
    *      summary="Transaction Status",
    *      description="Transaction Status API",
    *      security={ {"bearerAuth": {} }},
    *      @OA\RequestBody(
    *      @OA\MediaType(
    *           mediaType="application/x-www-form-urlencoded",
    *           @OA\Schema(
    *               required={"order_id"},
    *               @OA\Property(
    *                   property="order_id", 
    *                   type="string", 
    *                   example="RA00001"
    *               ),
    *           ),
    *       ),
    *       ),
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
    public function transactionStatus(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();
            
            return response($response, 200);
        }

        $paytmParams = array();

        $paytmParams["body"] = array(
            "mid"     => config('constant.paytm_merchant_id'),
            "orderId" => $request->order_id,
        );

        $checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), config('constant.paytm_merchant_key'));

        // $verifySignature = PaytmChecksum::verifySignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), config('constant.paytm_merchant_key'), $checksum);
        
        $paytmParams["head"] = array(
            "signature"    => $checksum
        );
        
        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
        
        $url = config('constant.paytm_gateway_base_url')."/v3/order/status";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); 
        $curlResponse = curl_exec($ch);
        
        if($curlResponse){
            $response['data'] = json_decode($curlResponse);
        }else{
            $response['status'] = false;
        }

        return response($response);
    }
}
