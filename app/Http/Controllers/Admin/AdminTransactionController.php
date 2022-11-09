<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Http\Controllers\Controller;
use App\Models\PaymentPayout;
use Illuminate\Http\Request;

class AdminTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PaymentPayout::query();
            $data = $data->where('role',config('constant.roles.admin'))
                ->with(['creator'])
                ->orderByDesc('id');
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('module_name', function($row){
                    $result = '';
                    $results = '';
                    if($row->module_name == 'master_class_direct') {
                        $result = 'Master Class';
                        $results = '<br /><small class="badge badge-info">Direct</small>';
                    } else if($row->module_name == 'master_class_affiliate') {
                        $result = 'Master Class';
                        $results = '<br /><small class="badge badge-secondary">Affiliate</small>';
                    } else if($row->module_name == 'call_booking') {
                        $result = 'Call Booking';
                    }
                    return $result.$results;
                })
                ->addColumn('module_title', function($row){
                    $result = '';
                    if($row->module_name == 'master_class_direct' || $row->module_name == 'master_class_affiliate') {
                        $result = $row->masterClasses->title;
                    } else if($row->module_name == 'call_booking') {
                        $call_bookings = $row->callBookings ?? '';
                        $result = $call_bookings ? $call_bookings->callPackage->name : '';
                    }
                    return $result;
                })
                ->filterColumn('creator_full_name', function($query, $keyword) {
                    $query->whereHas('creator', function($query) use ($keyword) {
                        $query->whereRaw("CONCAT(vFirstName,' ',vLastName) like ?", ["%{$keyword}%"]);
                    });
                })
                ->rawColumns(['module_name'])
                ->make(true);
        }

        return view('admin-transactions.index');
    }
}
