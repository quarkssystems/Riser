<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerCategoryRequest;
use App\Models\MasterBannerCategory;
use Illuminate\Http\Request;

class BannerCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = MasterBannerCategory::query();
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('status', function($row){
                    $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('banner-categories.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    // $btn .= '<a href="'.route('banner-categories.edit',[$row->id]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    // $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('banner-categories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        return view('banner-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBannerCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBannerCategoryRequest $request)
    {
        $input = $request->all();

        $bannerCategory = MasterBannerCategory::create($input);

        return redirect()->route('banner-categories.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MasterBannerCategory $bannerCategory
     * @return \Illuminate\Http\Response
     */
    public function show(MasterBannerCategory $bannerCategory)
    {
        return view('banner-categories.view', compact('bannerCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Banner $bannerCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterBannerCategory $bannerCategory)
    {
        return view('banner-categories.edit', compact('bannerCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreBannerCategoryRequest  $request
     * @param  \App\Models\Banner $bannerCategory
     * @return \Illuminate\Http\Response
     */
    public function update(StoreBannerCategoryRequest $request, MasterBannerCategory $bannerCategory)
    {
        $input = $request->all();

        $bannerCategory->update($input);

        return redirect()->route('banner-categories.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Banner $bannerCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(MasterBannerCategory $bannerCategory)
    {
        $bannerCategory->delete();
    }
    
    public function changeStatus(MasterBannerCategory $bannerCategory, Request $request)
    {
        $inputData = $request->all();

        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $bannerCategory->update(['status' => $status]);

        return true;
    }
}
