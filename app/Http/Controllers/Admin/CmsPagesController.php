<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCmsPageRequest;
use App\Models\CmsPages;
use Illuminate\Http\Request;

class CmsPagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CmsPages::query();
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('status', function($row){
                    $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('cms-pages.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="'.route('cms-pages.edit',[$row->id]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('cms-pages.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        return view('cms-pages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCmsPageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCmsPageRequest $request)
    {
        $input = $request->all();

        $cmsPage = CmsPages::create($input);

        return redirect()->route('cms-pages.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CmsPages $cmsPage
     * @return \Illuminate\Http\Response
     */
    public function show(CmsPages $cmsPage)
    {
        return view('cms-pages.view', compact('cmsPage'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CmsPages $cmsPage
     * @return \Illuminate\Http\Response
     */
    public function edit(CmsPages $cmsPage)
    {
        return view('cms-pages.edit', compact('cmsPage'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreCmsPageRequest  $request
     * @param  \App\Models\CmsPages $cmsPage
     * @return \Illuminate\Http\Response
     */
    public function update(StoreCmsPageRequest $request, CmsPages $cmsPage)
    {
        $input = $request->all();

        $cmsPage->update($input);

        return redirect()->route('cms-pages.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CmsPages $cmsPage
     * @return \Illuminate\Http\Response
     */
    public function destroy(CmsPages $cmsPage)
    {
        $cmsPage->delete();
    }
    
    public function changeStatus(CmsPages $cmsPage, Request $request)
    {
        $inputData = $request->all();

        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $cmsPage->update(['status' => $status]);

        return true;
    }
}
