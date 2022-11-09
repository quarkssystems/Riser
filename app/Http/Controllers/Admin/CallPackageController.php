<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCallPackageRequest;
use App\Models\CallPackage;
use Illuminate\Http\Request;

class CallPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CallPackage::query();
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('status', function($row){
                    $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('call-packages.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="'.route('call-packages.edit',[$row->id]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('call-packages.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('call-packages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCallPackageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCallPackageRequest $request)
    {
        $input = $request->all();
        $callPackage = CallPackage::create($input);

        return redirect()->route('call-packages.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CallPackage $callPackage
     * @return \Illuminate\Http\Response
     */
    public function show(CallPackage $callPackage)
    {
        return view('call-packages.view', compact('callPackage'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CallPackage $callPackage
     * @return \Illuminate\Http\Response
     */
    public function edit(CallPackage $callPackage)
    {
        return view('call-packages.edit', compact('callPackage'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreCallPackageRequest  $request
     * @param  \App\Models\CallPackage $callPackage
     * @return \Illuminate\Http\Response
     */
    public function update(StoreCallPackageRequest $request, CallPackage $callPackage)
    {
        $input = $request->all();
        $callPackage->update($input);

        return redirect()->route('call-packages.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CallPackage $callPackage
     * @return \Illuminate\Http\Response
     */
    public function destroy(CallPackage $callPackage)
    {
        $callPackage->delete();
    }
    
    public function changeStatus(CallPackage $callPackage, Request $request)
    {
        $inputData = $request->all();

        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $callPackage->update(['status' => $status]);

        return true;
    }
}
