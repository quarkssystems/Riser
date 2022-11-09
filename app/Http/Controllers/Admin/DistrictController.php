<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use Illuminate\Http\Request;
use App\Models\MasterCountry;
use App\Models\MasterState;
use App\Models\MasterDistrict;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDistrictRequest;

class DistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = MasterDistrict::with('state');
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('status', function($row){
                    $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('districts.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="'.route('districts.edit',[$row->id]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';

                    return $btn;
                })
                ->addColumn('state', function($row){
                    return $row->state->name ?? null;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('districts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countryData = MasterCountry::status()->get();
        $stateData = MasterState::status()->get();
        return view('districts.create', compact('stateData', 'countryData'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreDistrictRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDistrictRequest $request)
    {
        $input = $request->all();
        $district = MasterDistrict::create($input);
        
        return redirect()->route('districts.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MasterDistrict $district
     * @return \Illuminate\Http\Response
     */
    public function show(MasterDistrict $district)
    {
        return view('districts.view', compact('district'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MasterDistrict $district
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterDistrict $district)
    {
        $countryData = MasterCountry::status()->get();
        $stateData = MasterState::status()->get();
        return view('districts.edit', compact('district', 'stateData', 'countryData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreDistrictRequest  $request
     * @param  \App\Models\MasterDistrict $district
     * @return \Illuminate\Http\Response
     */
    public function update(StoreDistrictRequest $request, MasterDistrict $district)
    {
        $input = $request->all();
        $district->update($input);

        return redirect()->route('districts.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MasterDistrict $district
     * @return \Illuminate\Http\Response
     */
    public function destroy(MasterDistrict $district)
    {
        $district->delete();
    }
    
    public function changeStatus(MasterDistrict $district, Request $request)
    {
        $inputData = $request->all();
        
        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $district->update(['status' => $status]);

        return true;
    }
}
