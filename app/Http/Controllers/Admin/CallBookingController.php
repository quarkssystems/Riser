<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Http\Controllers\Controller;
use App\Models\CallBooking;
use Illuminate\Http\Request;

class CallBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CallBooking::query();
            $data = $data->with(['callPackage:id,name', 'user:iUserId,vFirstName,vLastName', 'creator:iUserId,vFirstName,vLastName'])->orderByDesc('id');
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('booking_date', function($row){
                    $result = $row->booking_date.' '.$row->start_time.' - '.$row->end_time;
                    return $result;
                })
                ->editColumn('status', function($row){
                    return ucfirst($row->status);
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('call-bookings.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    // $btn .= '<a href="'.route('call-bookings.edit',[$row->id]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';
                    
                    $btn .= '<a href="'.route('call-bookings.transactions',[$row->id]).'" class="ml-1 transactionsBtn btn btn-warning btn-xs" title="Transactions"><i class="fas fa-receipt"></i></a>';

                    return $btn;
                })
                ->rawColumns(['booking_date', 'status', 'action'])
                ->make(true);
        }

        return view('call-bookings.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CallBooking $callBooking
     * @return \Illuminate\Http\Response
     */
    public function show(CallBooking $callBooking)
    {
        $callBooking->load(['callPackage:id,name', 'user:iUserId,vFirstName,vLastName', 'creator:iUserId,vFirstName,vLastName']);
        return view('call-bookings.view', compact('callBooking'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CallBooking $callBooking
     * @return \Illuminate\Http\Response
     */
    public function destroy(CallBooking $callBooking)
    {
        $callBooking->delete();
    }

    public function changeStatus(CallBooking $callBooking, Request $request)
    {
        $inputData = $request->all();

        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $callBooking->update(['status' => $status]);

        return true;
    }

    public function getCallBOokingTransactions(CallBooking $callBooking, Request $request)
    {
        if ($request->ajax()) {
            $data = $callBooking->transaction()->with(['user:iUserId,vFirstName,vLastName']);
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view('call-bookings.transactions', compact('callBooking'));
    }
}
