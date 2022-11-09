<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class PaymentTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PaymentTransaction::query();
            $data = $data->with(['user', 'masterClasses' => function($query) {
                $query->select('id','title');
                $query->withTrashed();
                
            }, 'callBookings' => function($query) {
                $query->withTrashed();
                $query->with(['callPackage' => function($query) {
                    $query->withTrashed();
                }]);
                
            }, 'affiliateUser'])->orderByDesc('id');
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('payment_settled', function($row){
                    $colCls = $row->payment_settled == 'no' ? 'danger' : 'success';
                    return '<span role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->payment_settled).'</span>';
                })
                ->editColumn('status', function($row){
                    $status = $row->status;
                    if($status == config('constant.status.in_progress_value')) {
                        $colCls = 'info';
                    } else if($status == config('constant.status.cancelled_value')) {
                        $colCls = 'warning';
                    } else if($status == config('constant.status.failed_value')) {
                        $colCls = 'danger';
                    } else if($status == config('constant.status.completed_value')) {
                        $colCls = 'success';
                    } else {
                        $colCls = 'secondary';
                    }
                    
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('module_name', function($row){
                    $result = '';
                    $results = '';
                    if($row->master_class_id) {
                        $result = $row->masterClasses->title;
                        $results = '<br /><small class="badge badge-info">Master Class</small>';
                    } else if($row->call_booking_id) {
                        $call_bookings = $row->callBookings ?? '';
                        $result = $call_bookings ? $call_bookings->callPackage->name : '';
                        $results = '<br /><small class="badge badge-secondary">Call Booking</small>';
                    }
                    return $result.$results;
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('payment-transactions.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';

                    return $btn;
                })
                ->filterColumn('user_full_name', function($query, $keyword) {
                    $query->whereHas('user', function($query) use ($keyword) {
                        $query->whereRaw("CONCAT(vFirstName,' ',vLastName) like ?", ["%{$keyword}%"]);
                    });
                })
                ->filterColumn('affiliate_user_full_name', function($query, $keyword) {
                    $query->whereHas('affiliateUser', function($query) use ($keyword) {
                        $query->whereRaw("CONCAT(vFirstName,' ',vLastName) like ?", ["%{$keyword}%"]);
                    });
                })
                ->filterColumn('module_name', function($query, $keyword) {
                    $query->whereHas('masterClasses', function($query) use ($keyword) {
                        $query->where('title', 'LIKE', '%'.$keyword.'%');
                    })
                    ->orWhereHas('callBookings', function($query) use ($keyword) {
                        $query->whereHas('callPackage', function($query) use ($keyword) {
                            $query->where('name', 'LIKE', '%'.$keyword.'%');
                        });
                    });
                })
                ->rawColumns(['payment_settled', 'module_name', 'status', 'action'])
                ->make(true);
        }

        return view('payment-transactions.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaymentTransaction $paymentTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentTransaction $paymentTransaction)
    {
        $paymentTransaction->load(['user', 'masterClasses'  => function($query) {
            $query->select('id','title');
            $query->withTrashed();
            
        }, 'callBookings' => function($query) {
            $query->withTrashed();
            $query->with(['callPackage' => function($query) {
                $query->withTrashed();
            }]);   
        }, 'affiliateUser']);
        return view('payment-transactions.view', compact('paymentTransaction'));
    }
}
