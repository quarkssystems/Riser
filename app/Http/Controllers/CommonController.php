<?php

namespace App\Http\Controllers;

use App\Models\MasterDistrict;
use App\Models\MasterState;
use App\Models\MasterTaluka;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommonController extends Controller
{
    public function getStatesList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return back()->with('errors', $validator->errors());
        }

        $data = MasterState::where('country_id', $request->country_id)->orderBy('name','asc')->status()->get();

        return $data;
    }
    
    public function getDistrictsList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'state_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return back()->with('errors', $validator->errors());
        }

        $data = MasterDistrict::where('state_id', $request->state_id)->orderBy('name','asc')->status()->get();

        return $data;
    }
    
    public function getTalukasList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'district_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return back()->with('errors', $validator->errors());
        }

        $data = MasterTaluka::where('district_id', $request->district_id)->orderBy('name','asc')->status()->get();

        return $data;
    }

    public function getUsersList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_roles' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('errors', $validator->errors());
        }

        $roles = explode(',',$request->user_roles);

        $data = User::select('iUserId', 'vFirstName', 'vLastName')->status();

        if($request->filled('search_term')) {
            $data->whereRaw("CONCAT(vFirstName,' ',vLastName) like ?", ["%{$request->search_term}%"]);
        }

        $data = $data->whereHas(
            'roles', function($q) use($roles){
                $q->whereIn('name', $roles);
            }
        )->get();

        return $data;
    }
}
