<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\CallBooking;
use App\Models\MasterClass;
use Illuminate\Support\Str;
use App\Models\PaymentPayout;
use App\Models\PaymentPercentage;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

if (!function_exists('getDisk')) {
    function getDisk(){
        return env('STORAGE_DISK','public');   
    }
}

if (!function_exists('storeFile')) {
	function storeFile($fileDir, $file, $fileName = null) 
	{
        if($fileName){
			$storagePath = Storage::disk(getDisk())->putFileAs($fileDir, $file, $fileName);
		}else{
			$storagePath = Storage::disk(getDisk())->put($fileDir, $file);
		}
		return $storagePath;
	}
}

if (!function_exists('fileExists')) {
	function fileExists($file) 
	{
		return (Storage::disk(getDisk())->exists($file) == TRUE) ? TRUE : FALSE; 
	}
}

if (!function_exists('deleteFile')) {
	function deleteFile($file) 
	{
		return Storage::disk(getDisk())->delete($file);
	}
}

if (!function_exists('getFileURL')) {
	function getFileURL($file)
	{
		return is_null($file) ? null : Storage::disk(getDisk())->url($file);
	}
}

if (!function_exists('getFilePath')) {
	function getFilePath($fileDir, $file)
	{
		$path = $fileDir."/".$file;
		return Storage::disk(getDisk())->url($path);
	}
}

if (!function_exists('thousandsFormat')) {
    function thousandsFormat($num) {

		if($num>=1000) {
	  
			  $x = round($num);
			  $x_number_format = number_format($x);
			  $x_array = explode(',', $x_number_format);
			  $x_parts = array('k', 'm', 'b', 't');
			  $x_count_parts = count($x_array) - 1;
			  $x_display = $x;
			  $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
			  $x_display .= $x_parts[$x_count_parts - 1];
	  
			  return $x_display;
	  
		}
	  
		return $num;
	}
}

if (! function_exists('getSelectOptions')) {
    function getSelectOptions($data = [], $selected = null)
    {
        $result = '';
        if(!is_array($data)) {
            return false;
        }
        foreach($data as $key => $value) {
            $sel = ($selected == $key) ? "selected" : "";
            $result .= '<option value="'.$key.'" '.$sel.' > '. ucfirst($value) .'</options>';
        }

        return $result;
    }
}

if (! function_exists('getPaymentPercentage')) {
    function getPaymentPercentage($moduleName = null)
    {
		$percentageArr = array();

		if($moduleName){
			$paymentPercentages = PaymentPercentage::where('module_name',$moduleName)->status()->get();

			$creatorRole = config('constant.roles.creator');
			$agentRole = config('constant.roles.agent');
			$dlRole = config('constant.roles.district-leader');
			$slRole = config('constant.roles.state-leader');
			$coreTeamRole = config('constant.roles.core-team');
			$adminRole = config('constant.roles.admin');
			$affiliatorRole = 'affiliator';

			foreach ($paymentPercentages as $paymentPercentage) {
				if($paymentPercentage->role == $creatorRole){
					$percentageArr['creatorCut'] = $paymentPercentage->hiddent_cut_percent;
					$percentageArr['creatorPercent'] = $paymentPercentage->percentage;
					$percentageArr['creatorParent'] = $paymentPercentage->parent_role;
				}
				if($paymentPercentage->role == $agentRole){
					$percentageArr['agentCut'] = $paymentPercentage->hiddent_cut_percent;
					$percentageArr['agentPercent'] = $paymentPercentage->percentage;
					$percentageArr['agentParent'] = $paymentPercentage->parent_role;
				}
				if($paymentPercentage->role == $dlRole){
					$percentageArr['dLeaderCut'] = $paymentPercentage->hiddent_cut_percent;
					if($paymentPercentage->parent_role == $agentRole){
						$percentageArr['dLeaderPercentAgent'] = $paymentPercentage->percentage;
						$percentageArr['dLeaderParentAgent'] = $paymentPercentage->parent_role;
					}else if($paymentPercentage->parent_role == $creatorRole){
						$percentageArr['dLeaderPercentCreator'] = $paymentPercentage->percentage;
						$percentageArr['dLeaderParentCreator'] = $paymentPercentage->parent_role;
					}
				}
				if($paymentPercentage->role == $slRole){
					$percentageArr['sLeaderCut'] = $paymentPercentage->hiddent_cut_percent;
					if($paymentPercentage->parent_role == $agentRole){
						$percentageArr['sLeaderPercentAgent'] = $paymentPercentage->percentage;
						$percentageArr['sLeaderParentAgent'] = $paymentPercentage->parent_role;
					}else if($paymentPercentage->parent_role == $creatorRole){
						$percentageArr['sLeaderPercentCreator'] = $paymentPercentage->percentage;
						$percentageArr['sLeaderParentCreator'] = $paymentPercentage->parent_role;
					}else if($paymentPercentage->parent_role == $dlRole){
						$percentageArr['sLeaderPercentDl'] = $paymentPercentage->percentage;
						$percentageArr['sLeaderParentDl'] = $paymentPercentage->parent_role;
					}
				}

				if($paymentPercentage->role == $coreTeamRole){
					if($paymentPercentage->parent_role == $creatorRole){
						$percentageArr['coreTeamPercentCreator'] = $paymentPercentage->percentage;
						$percentageArr['coreTeamParentCreator'] = $paymentPercentage->parent_role;
					}else if($paymentPercentage->parent_role == $dlRole){
						$percentageArr['coreTeamPercentDl'] = $paymentPercentage->percentage;
						$percentageArr['coreTeamParentDl'] = $paymentPercentage->parent_role;
					}else if($paymentPercentage->parent_role == $slRole){
						$percentageArr['coreTeamPercentSl'] = $paymentPercentage->percentage;
						$percentageArr['coreTeamParentSl'] = $paymentPercentage->parent_role;
					}
				}

				if($paymentPercentage->role == $adminRole){
					if($paymentPercentage->parent_role == $creatorRole){
						$percentageArr['adminPercentCreator'] = $paymentPercentage->percentage;
						$percentageArr['adminParentCreator'] = $paymentPercentage->parent_role;
					}else if($paymentPercentage->parent_role == $slRole){
						$percentageArr['adminPercentSl'] = $paymentPercentage->percentage;
						$percentageArr['adminParentSl'] = $paymentPercentage->parent_role;
					}else {
						$percentageArr['adminPercent'] = $paymentPercentage->percentage;
						$percentageArr['adminParent'] = $paymentPercentage->parent_role;
					}
				}

				if($paymentPercentage->role == $affiliatorRole){
					$percentageArr['affiliatorPercent'] = $paymentPercentage->percentage;
					$percentageArr['affiliatorParent'] = $paymentPercentage->parent_role;
				}
				
			}
		}
				
		return $percentageArr;
    }
}

if (! function_exists('paymentSettle')) {
    function paymentSettle($transaction, $percentageArr, $moduleName)
    {
		$balanceTotal = $transaction->total;
		
		//Creator
		$creatorAmount = $transaction->total * $percentageArr['creatorPercent']/100;
		$creatorCut = $creatorAmount * $percentageArr['creatorCut']/100;
		$creatorFinalAmount = $creatorAmount - $creatorCut;

		//Agent
		$agentAmount = $creatorAmount * $percentageArr['agentPercent']/100;
		$agentCut = $agentAmount * $percentageArr['agentCut']/100;
		$agentFinalAmount = $agentAmount - $agentCut;

		//District Leader
		$dLeaderAmount = ($creatorAmount * $percentageArr['dLeaderPercentCreator']/100) + ($agentAmount * $percentageArr['dLeaderPercentAgent']/100);
		$dLeaderCut = $dLeaderAmount * $percentageArr['dLeaderCut']/100;
		$dLeaderFinalAmount = $dLeaderAmount - $dLeaderCut;

		//State Leader
		$sLeaderAmount = ($creatorAmount * $percentageArr['sLeaderPercentCreator']/100) + ($agentAmount * $percentageArr['sLeaderPercentAgent']/100) + ($dLeaderAmount * $percentageArr['sLeaderPercentDl']/100);
		$sLeaderCut = $sLeaderAmount * $percentageArr['sLeaderCut']/100;
		$sLeaderFinalAmount = $sLeaderAmount - $sLeaderCut;

		//Core Team member
		$coreTeamAmount = ($creatorAmount * $percentageArr['coreTeamPercentCreator']/100) + ($dLeaderAmount * $percentageArr['coreTeamPercentDl']/100) + ($sLeaderAmount * $percentageArr['coreTeamPercentSl']/100);

		if($moduleName == 'master_class_affiliate'){
			//Affiliator
			$affiliatorAmount = $transaction->total * $percentageArr['affiliatorPercent']/100;
			$affiliatorCut = 0; //TODO if added in future
			$affiliatorFinalAmount = $affiliatorAmount - $affiliatorCut;
		}

		if($moduleName == 'master_class_direct' || $moduleName == 'master_class_affiliate'){
			$masterClass = MasterClass::select('id', 'user_id')->where('id', $transaction->master_class_id)->with(['user:iUserId,vTeamId'])->first();
			$userDetail = $masterClass->user;
			$creatorId = $masterClass->user_id;
			$moduleId = $transaction->master_class_id;
		}else if($moduleName == 'call_booking'){
			$callBooking = CallBooking::select('id', 'creator_id')->where('id', $transaction->call_booking_id)->with(['creator:iUserId,vTeamId'])->first();
			$userDetail = $callBooking->creator;
			$creatorId = $callBooking->creator_id;
			$moduleId = $transaction->call_booking_id;
		}
		$userIds = explode('-',$userDetail->vTeamId);
		$otherUsers = User::select('iUserId')->whereIn('iUserId',$userIds)->status()->orderBy('iUserId', 'DESC')->get();


		//Deposite for creator
		$creatorPayout = new PaymentPayout();
		$creatorPayout->user_id = $creatorId;
		$creatorPayout->module_name = $moduleName;
		$creatorPayout->module_id = $moduleId;
		$creatorPayout->creator_id = $creatorId;
		$creatorPayout->role = config('constant.roles.creator');
		$creatorPayout->percentage = $percentageArr['creatorPercent'];
		$creatorPayout->payout_amount = $creatorFinalAmount;
		$creatorPayout->save();
		$userDetail->deposit($creatorFinalAmount*100);
		$balanceTotal = $balanceTotal > 0 ? $balanceTotal - $creatorFinalAmount : 0;

		$agentPaid = $dLeaderPaid = $sLeaderPaid = $coreTeamPaid = false;
		$previousRole = $previousId = NULL;

		$creatorParentData = $agentParentData = $distParentData = [];
		$agentRecId = $distRecId = $stateRecId = null;
		$creatorRecId = $creatorPayout->id ?? null; 
		foreach ($otherUsers as $otherUser) {

			$userPayout = new PaymentPayout();
			$userPayout->user_id = $otherUser->iUserId;
			$userPayout->module_name = $moduleName;
			$userPayout->module_id = $moduleId;
			$userPayout->creator_id = $creatorId;			

			if($otherUser->hasRole('agent') && $agentPaid == false){
				
				$userPayout->role = config('constant.roles.agent');
				$userPayout->percentage = $percentageArr['agentPercent'];
				$userPayout->payout_amount = $agentFinalAmount;
				$userPayout->save();
				$otherUser->deposit($agentFinalAmount*100);
				$agentPaid = true;
				$balanceTotal = $balanceTotal > 0 ? $balanceTotal - $agentFinalAmount : 0;

				$agentRecId = $userPayout->id;
				//for parent data
				$creatorParentData['parent_user_id'] = $userPayout->user_id;
				$creatorParentData['parent_role'] = $userPayout->role;
				$creatorParentData['parent_percentage'] = $userPayout->percentage;
				$creatorParentData['parent_payout_amount'] = $userPayout->payout_amount;

			}else if($otherUser->hasRole('district-leader') && $dLeaderPaid == false){
				
				$userPayout->role = config('constant.roles.district-leader');
				$userPayout->percentage = $percentageArr['dLeaderPercentCreator']+$percentageArr['dLeaderPercentAgent'];
				$userPayout->payout_amount = $dLeaderFinalAmount;
				$userPayout->save();
				$otherUser->deposit($dLeaderFinalAmount*100);
				$dLeaderPaid = true;
				$balanceTotal = $balanceTotal > 0 ? $balanceTotal - $dLeaderFinalAmount : 0;

				$distRecId = $userPayout->id;
				//for parent data
				$agentParentData['parent_user_id'] = $userPayout->user_id;
				$agentParentData['parent_role'] = $userPayout->role;
				$agentParentData['parent_percentage'] = $userPayout->percentage;
				$agentParentData['parent_payout_amount'] = $userPayout->payout_amount;

			}else if($otherUser->hasRole('state-leader') && $sLeaderPaid == false){
				
				$userPayout->role = config('constant.roles.state-leader');
				$userPayout->percentage = $percentageArr['sLeaderPercentCreator']+$percentageArr['sLeaderPercentAgent']+$percentageArr['sLeaderPercentDl'];
				$userPayout->payout_amount = $sLeaderFinalAmount;
				$userPayout->save();
				$otherUser->deposit($sLeaderFinalAmount*100);
				$sLeaderPaid = true;
				$balanceTotal = $balanceTotal > 0 ? $balanceTotal - $sLeaderFinalAmount : 0;

				$stateRecId = $userPayout->id;
				//for parent data
				$distParentData['parent_user_id'] = $userPayout->user_id;
				$distParentData['parent_role'] = $userPayout->role;
				$distParentData['parent_percentage'] = $userPayout->percentage;
				$distParentData['parent_payout_amount'] = $userPayout->payout_amount;

			}else if($otherUser->hasRole('core-team') && $coreTeamPaid == false){
				
				$userPayout->role = config('constant.roles.core-team');
				$userPayout->percentage = $percentageArr['coreTeamPercentCreator']+$percentageArr['coreTeamPercentDl']+$percentageArr['coreTeamPercentSl'];
				$userPayout->payout_amount = $coreTeamAmount;
				$userPayout->save();
				$otherUser->deposit($coreTeamAmount*100);
				$coreTeamPaid = true;
				$balanceTotal = $balanceTotal > 0 ? $balanceTotal - $coreTeamAmount : 0;
				
				//for parent data
				$stateParentData['parent_user_id'] = $userPayout->user_id;
				$stateParentData['parent_role'] = $userPayout->role;
				$stateParentData['parent_percentage'] = $userPayout->percentage;
				$stateParentData['parent_payout_amount'] = $userPayout->payout_amount;
			}
		}

		//for creator
		if($creatorRecId && $creatorParentData) {
			PaymentPayout::where('id', $creatorRecId)->update($creatorParentData);
		}

		//for agent
		if($agentRecId && $agentParentData) {
			PaymentPayout::where('id', $agentRecId)->update($agentParentData);
		}

		//for dist
		if($distRecId && $distParentData) {
			PaymentPayout::where('id', $distRecId)->update($distParentData);
		}

		//for state
		if($stateRecId && $stateParentData) {
			PaymentPayout::where('id', $stateRecId)->update($stateParentData);
		}
		
		if($moduleName == 'master_class_affiliate'){
			$affiliateUser = User::select('iUserId')->where('iUserId',$transaction->affiliate_user_id)->status()->first();

			$affiliatorPayout = new PaymentPayout();
			$affiliatorPayout->user_id = $affiliateUser->iUserId;
			$affiliatorPayout->module_name = $moduleName;
			$affiliatorPayout->module_id = $moduleId;
			$affiliatorPayout->creator_id = $creatorId;
			$affiliatorPayout->role = 'affiliator';
			$affiliatorPayout->percentage = $percentageArr['affiliatorPercent'];
			$affiliatorPayout->payout_amount = $affiliatorFinalAmount;
			$affiliatorPayout->save();
			$affiliateUser->deposit($affiliatorFinalAmount*100);
			$balanceTotal = $balanceTotal > 0 ? $balanceTotal - $affiliatorFinalAmount : 0;
		}

		//Deposite for admin
		$admin = User::whereHas(
			'roles', function($q){
				$q->where('name', 'superadmin');
			}
		)->first();
		$adminAmount = $balanceTotal;
		$adminPayout = new PaymentPayout();
		$adminPayout->user_id = $admin->iUserId;
		$adminPayout->module_name = $moduleName;
		$adminPayout->module_id = $moduleId;
		$adminPayout->creator_id = $creatorId;
		$adminPayout->role = config('constant.roles.admin');
		$adminPayout->percentage = $percentageArr['adminPercent']+$percentageArr['adminPercentCreator']+$percentageArr['adminPercentSl'];
		$adminPayout->payout_amount = $adminAmount;
		$adminPayout->save();
		$admin->deposit($adminAmount*100);
		
		if($moduleName == 'master_class_direct' || $moduleName == 'master_class_affiliate'){
			$paymentSettledStatus = PaymentTransaction::where('master_class_id', $moduleId);
			
			if($moduleName == 'master_class_affiliate'){
				$paymentSettledStatus->whereNotNull('affiliate_user_id');
			}else if($moduleName == 'master_class_direct'){
				$paymentSettledStatus->whereNull('affiliate_user_id');
			}
			$paymentSettledStatus = $paymentSettledStatus->update([
				'payment_settled' => 'yes'
			]);

			$masterClassSettled = PaymentTransaction::where('master_class_id', $moduleId)->where('payment_settled', 'no')->first();

			if(!$masterClassSettled){
				//Update Master Class status
				$masterClass->payment_settled = 'yes';
				$masterClass->save();
			}
		}else if($moduleName == 'call_booking'){
			$paymentSettledStatus = PaymentTransaction::where('call_booking_id', $moduleId);
			
			$paymentSettledStatus = $paymentSettledStatus->update([
				'payment_settled' => 'yes'
			]);

			$callBookingSettled = PaymentTransaction::where('call_booking_id', $moduleId)->where('payment_settled', 'no')->first();

			if(!$callBookingSettled){
				//Update Call Booking status
				$callBooking->payment_settled = 'yes';
				$callBooking->save();
			}

		}
		
	}
}

if (! function_exists('generateReferralCode')) {
    function generateReferralCode($length = 8)
    {
        $myCodeFound = 1;
		$myCode = "";
		
        while($myCodeFound == 1){
        	$myCode = substr(md5(uniqid(mt_rand(), true)) , 0, $length);
			$existingCode = User::where('vMyCode', $myCode)->first();
        	if($existingCode){
				$myCodeFound = 1;
			} else {
				$myCodeFound = 0;
			}
        }
        return strtoupper($myCode);
    }
}

if (! function_exists('filterByDates')) {
    function filterByDates($q, $request){

		if ($request->filter_by == config('constant.filter_by.today')) {
			$today = Carbon::now()->format('Y-m-d');
			$q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$today."'");                   
		}

		if ($request->filter_by == config('constant.filter_by.yesterday')) {
			$yesterday = Carbon::yesterday()->format('Y-m-d');
			$q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') = '".$yesterday."'");
		}

		if ($request->filter_by == config('constant.filter_by.7-day')) {
			$today = Carbon::now()->format('Y-m-d');
			$sevenDays = Carbon::now()->subDays(7)->format('Y-m-d');
			$q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') > '".$sevenDays."'");
			$q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m-%d') <= '".$today."'");
		}

		if ($request->filter_by == config('constant.filter_by.this-month')) {
			$thisMonth = Carbon::now()->format('Y-m');
			$q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$thisMonth."'");
		}

		if ($request->filter_by == config('constant.filter_by.last-month')) {
			$lastMonth = Carbon::now()->subMonth()->format('Y-m');
			$q->whereRaw("DATE_FORMAT(master_class_users.created_at, '%Y-%m') = '".$lastMonth."'");
		}

		return $q;
	}
}