<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use Illuminate\Http\Request;
use App\Models\MasterCountry;
use App\Models\MasterState;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStateRequest;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = MasterState::query();
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('status', function($row){
                    $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('states.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="'.route('states.edit',[$row->id]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';

                    return $btn;
                })
                ->editColumn('country_id', function($row){
                    return $row->country->name ?? null;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('states.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countryData = MasterCountry::status()->get();
        return view('states.create', compact('countryData'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStateRequest $request)
    {
        $input = $request->all();
        $state = MasterState::create($input);
        
        return redirect()->route('states.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MasterState $state
     * @return \Illuminate\Http\Response
     */
    public function show(MasterState $state)
    {
        return view('states.view', compact('state'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MasterState $state
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterState $state)
    {
        $countryData = MasterCountry::status()->get();
        return view('states.edit', compact('state', 'countryData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreStateRequest  $request
     * @param  \App\Models\MasterState $state
     * @return \Illuminate\Http\Response
     */
    public function update(StoreStateRequest $request, MasterState $state)
    {
        $input = $request->all();
        $state->update($input);

        return redirect()->route('states.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MasterState $state
     * @return \Illuminate\Http\Response
     */
    public function destroy(MasterState $state)
    {
        $state->delete();
    }
    
    public function changeStatus(MasterState $state, Request $request)
    {
        $inputData = $request->all();
        
        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $state->update(['status' => $status]);

        return true;
    }
}
