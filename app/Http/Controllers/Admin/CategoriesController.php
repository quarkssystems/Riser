<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\MasterCategories;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = MasterCategories::query();
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('status', function($row){
                    $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('categories.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="'.route('categories.edit',[$row->id]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('categories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorecategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorecategoryRequest $request)
    {
        $input = $request->all();

        if($request->hasFile('category_image')) {
            $fileName = storeFile('category-images', $request->category_image);
            $input['category_image'] = $fileName;
        }

        $category = MasterCategories::create($input);

        return redirect()->route('categories.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MasterCategories $category
     * @return \Illuminate\Http\Response
     */
    public function show(MasterCategories $category)
    {
        return view('categories.view', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MasterCategories $category
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterCategories $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreCategoryRequest  $request
     * @param  \App\Models\MasterCategories $category
     * @return \Illuminate\Http\Response
     */
    public function update(StoreCategoryRequest $request, MasterCategories $category)
    {
        $input = $request->all();

        if($request->hasFile('category_image')){
            if(fileExists($category->category_image)) {
                deleteFile($category->category_image);
            }

            $fileName = storeFile('category-images', $request->category_image);
            $input['category_image'] = $fileName;
        }

        $category->update($input);

        return redirect()->route('categories.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MasterCategories $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(MasterCategories $category)
    {
        if(fileExists($category->category_image)) {
            deleteFile($category->category_image);
        }

        $category->delete();
    }
    
    public function changeStatus(MasterCategories $category, Request $request)
    {
        $inputData = $request->all();

        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $category->update(['status' => $status]);

        return true;
    }
}
