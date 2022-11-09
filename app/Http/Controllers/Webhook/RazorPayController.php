<?php

namespace App\Http\Controllers\Webhook;

use Illuminate\Http\Request;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class RazorPayController extends Controller
{
    public function transactionStatus(Request $request)
    {
        Log::info("RazorPay transaction info: ".json_encode($request->all()));

        if(isset($request) && $request->payload && $request->payload['payment']['entity']['id']){
            $transaction = PaymentTransaction::where('transaction_id',$request->payload['payment']['entity']['id'])
            ->first();

            if($transaction){
                $transaction->update([
                    'status' => config('constant.status.completed_value'),
                ]);
            }

            return true;
        }else{
            Log::info("RazorPay transaction not found: ".$request->getContent());
            return false;
        }
    }
}
