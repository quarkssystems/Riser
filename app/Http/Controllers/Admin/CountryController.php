<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use Illuminate\Http\Request;
use App\Models\MasterCountry;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCountryRequest;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = MasterCountry::query();
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('status', function($row){
                    $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('countries.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="'.route('countries.edit',[$row->id]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('countries.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countryData = MasterCountry::status()->get();
        return view('countries.create', compact('countryData'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCountryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCountryRequest $request)
    {
        $input = $request->all();
        $country = MasterCountry::create($input);

        return redirect()->route('countries.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MasterCountry $country
     * @return \Illuminate\Http\Response
     */
    public function show(MasterCountry $country)
    {
        return view('countries.view', compact('country'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MasterCountry $country
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterCountry $country)
    {
        return view('countries.edit', compact('country'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreCountryRequest  $request
     * @param  \App\Models\MasterCountry $country
     * @return \Illuminate\Http\Response
     */
    public function update(StoreCountryRequest $request, MasterCountry $country)
    {
        $input = $request->all();
        $country->update($input);

        return redirect()->route('countries.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MasterCountry $country
     * @return \Illuminate\Http\Response
     */
    public function destroy(MasterCountry $country)
    {
        $country->delete();
    }
    
    public function changeStatus(MasterCountry $country, Request $request)
    {
        $inputData = $request->all();

        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $country->update(['status' => $status]);

        return true;
    }
}
