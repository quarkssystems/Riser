<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Models\MasterBannerCategory;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Banner::query();
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('status', function($row){
                    $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('banners.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="'.route('banners.edit',[$row->id]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('banners.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $bannerData = Banner::status()->get();
        $categories = MasterBannerCategory::status()->get();
        
        return view('banners.create', compact('bannerData', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBannerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBannerRequest $request)
    {
        $input = $request->all();

        if($request->hasFile('banner_image')){
            $fileName = storeFile('banner-images', $request->banner_image);
            $input['banner_image'] = $fileName;
        }

        $banner = Banner::create($input);

        if($banner && $request->filled('category_id')) {
            $banner->bannerCategories()->sync($input['category_id']);
        }

        return redirect()->route('banners.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Banner $banner
     * @return \Illuminate\Http\Response
     */
    public function show(Banner $banner)
    {
        $banner->load('bannerCategories');
        return view('banners.view', compact('banner'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Banner $banner
     * @return \Illuminate\Http\Response
     */
    public function edit(Banner $banner)
    {
        $banner->load('bannerCategories');
        $categories = MasterBannerCategory::status()->get();
        return view('banners.edit', compact('banner', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreBannerRequest  $request
     * @param  \App\Models\Banner $banner
     * @return \Illuminate\Http\Response
     */
    public function update(StoreBannerRequest $request, Banner $banner)
    {
        $input = $request->all();

        if($request->hasFile('banner_image')){
            if(fileExists($banner->banner_image)) {
                deleteFile($banner->banner_image);
            }

            $fileName = storeFile('banner-images', $request->banner_image);
            $input['banner_image'] = $fileName;
        }

        if($request->filled('category_id')) {
            $banner->bannerCategories()->sync($input['category_id']);
        }

        $banner->update($input);

        return redirect()->route('banners.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Banner $banner
     * @return \Illuminate\Http\Response
     */
    public function destroy(Banner $banner)
    {
        
        if(fileExists($banner->banner_image)) {
            deleteFile($banner->banner_image);
        }

        //remove categories from pivot table
        $banner->bannerCategories()->detach();
        $banner->delete();
    }
    
    public function changeStatus(Banner $banner, Request $request)
    {
        $inputData = $request->all();

        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $banner->update(['status' => $status]);

        return true;
    }
}
