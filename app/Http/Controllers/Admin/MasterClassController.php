<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Http\Controllers\Controller;
use App\Models\MasterClass;
use Illuminate\Http\Request;

class MasterClassController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = MasterClass::query();
            $data = $data->with('user')->orderByDesc('id');
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('start_date', function($row){
                    $result = $row->start_date.' '.$row->start_time.' - '.$row->end_time;
                    return $result;
                })
                ->editColumn('status', function($row){
                    $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('master-classes.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    // $btn .= '<a href="'.route('master-classes.edit',[$row->id]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';
                    
                    $btn .= '<a href="'.route('master-classes.users',[$row->id]).'" class="ml-1 usersBtn btn btn-secondary btn-xs" title="Users"><i class="fas fa-users"></i></a>';
                    $btn .= '<a href="'.route('master-classes.promoters',[$row->id]).'" class="ml-1 promotorsBtn btn btn-info btn-xs" title="Promoters"><i class="fas fa-users"></i></a>';
                    $btn .= '<a href="'.route('master-classes.affilitors',[$row->id]).'" class="ml-1 affilitorsBtn btn btn-dark btn-xs" title="Affilitors"><i class="fas fa-users"></i></a>';
                    $btn .= '<a href="'.route('master-classes.transactions',[$row->id]).'" class="ml-1 transactionsBtn btn btn-warning btn-xs" title="Transactions"><i class="fas fa-receipt"></i></a>';

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('master-classes.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MasterClass $masterClass
     * @return \Illuminate\Http\Response
     */
    public function show(MasterClass $masterClass)
    {
        $masterClass->load('user', 'categories');
        return view('master-classes.view', compact('masterClass'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MasterClass $masterClass
     * @return \Illuminate\Http\Response
     */
    public function destroy(MasterClass $masterClass)
    {
        if(fileExists($masterClass->banner_image)) {
            deleteFile($masterClass->banner_image);
        }
        
        $masterClass->userMasterClass()->detach();
        $masterClass->promoteMasterClass()->detach();
        $masterClass->myAffilitorUsers()->detach();
        // $masterClass->paymentTransactions()->delete();
        $masterClass->categories()->detach();
        $masterClass->delete();
    }

    public function changeStatus(MasterClass $masterClass, Request $request)
    {
        $inputData = $request->all();

        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $masterClass->update(['status' => $status]);

        return true;
    }

    public function getMasterClassUsers(MasterClass $masterClass, Request $request)
    {
        if ($request->ajax()) {
            $data = $masterClass->userMasterClassAdmin();
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view('master-classes.users', compact('masterClass'));
    }

    public function getMasterClassPromoters(MasterClass $masterClass, Request $request)
    {
        if ($request->ajax()) {
            $data = $masterClass->promoteMasterClass();
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view('master-classes.promoters', compact('masterClass'));
    }
    
    public function getMasterClassAffilitors(MasterClass $masterClass, Request $request)
    {
        if ($request->ajax()) {
            $data = $masterClass->myAffilitorUsers();
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view('master-classes.affilitors', compact('masterClass'));
    }
    
    public function getMasterClassTransactions(MasterClass $masterClass, Request $request)
    {
        if ($request->ajax()) {
            $data = $masterClass->paymentTransactions()->with(['user','affiliateUser']);
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view('master-classes.transactions', compact('masterClass'));
    }
}
