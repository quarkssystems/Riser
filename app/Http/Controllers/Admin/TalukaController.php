<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use Illuminate\Http\Request;
use App\Models\MasterCountry;
use App\Models\MasterState;
use App\Models\MasterDistrict;
use App\Models\MasterTaluka;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTalukaRequest;

class TalukaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = MasterTaluka::with(['district', 'district.state']);
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('status', function($row){
                    $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('talukas.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="'.route('talukas.edit',[$row->id]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('talukas.index');
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
        $districtData = MasterDistrict::status()->get();
        return view('talukas.create', compact('districtData', 'stateData', 'countryData'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTalukaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTalukaRequest $request)
    {
        $input = $request->all();
        $taluka = MasterTaluka::create($input);
        
        return redirect()->route('talukas.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MasterTaluka $taluka
     * @return \Illuminate\Http\Response
     */
    public function show(MasterTaluka $taluka)
    {
        return view('talukas.view', compact('taluka'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MasterTaluka $taluka
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterTaluka $taluka)
    {
        $countryData = MasterCountry::status()->get();
        $stateData = MasterState::status()->get();
        $districtData = MasterDistrict::status()->get();
        return view('talukas.edit', compact('taluka', 'districtData', 'stateData', 'countryData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreTalukaRequest  $request
     * @param  \App\Models\MasterTaluka $taluka
     * @return \Illuminate\Http\Response
     */
    public function update(StoreTalukaRequest $request, MasterTaluka $taluka)
    {
        $input = $request->all();
        $taluka->update($input);

        return redirect()->route('talukas.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MasterTaluka $taluka
     * @return \Illuminate\Http\Response
     */
    public function destroy(MasterTaluka $taluka)
    {
        $taluka->delete();
    }
    
    public function changeStatus(MasterTaluka $taluka, Request $request)
    {
        $inputData = $request->all();
        
        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $taluka->update(['status' => $status]);

        return true;
    }
}
