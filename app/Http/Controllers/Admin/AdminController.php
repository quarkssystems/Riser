<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Models\MasterCountry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::query();
            $data = $data->whereHas(
                'roles', function($q){
                    $q->whereIn('name', [config('constant.roles.admin')]);
                }
            )->select('iUserId', 'vFirstName', 'vLastName', 'vEmail', 'vPhoneNumber', 'whatsapp_number', 'vPassword', 'status')->orderByDesc('iUserId');
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('vPassword', function($row){
                    return $row->vPassword;
                })
                ->editColumn('status', function($row){
                    $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    return '<span data-id="'.$row->iUserId.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('admins.show',[$row->iUserId]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="'.route('admins.edit',[$row->iUserId]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->iUserId.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('users.admin.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countryData = MasterCountry::status()->get();
        return view('users.admin.create', compact('countryData'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAdminRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAdminRequest $request)
    {
        $input = $request->all();

        $input['vFirstName'] = $request->first_name;
        $input['vLastName'] = $request->last_name;
        $input['vEmail'] = $request->email;
        $input['username'] = $request->username;
        $input['vOccupation'] = $request->profession;
        $input['vPhoneNumber'] = $request->contact_number;
        $input['whatsapp_number'] = $request->whatsapp_number;
        $input['tAboutMe'] = $request->about_me;
        $input['vSkill'] = $request->user_skills;
        $input['vExperience'] = $request->user_experience;
        $input['business_name'] = $request->business_name;
        $input['country_id'] = $request->country_id;
        $input['state_id'] = $request->state_id;
        $input['facebook_link'] = $request->facebook_link;
        $input['twitter_link'] = $request->twitter_link;
        $input['linkedin_link'] = $request->linkedin_link;
        $input['instagram_link'] = $request->instagram_link;
        $input['youtube_link'] = $request->youtube_link;
        $input['status'] = $request->status;

        if($request->filled('gender')){
            $input['eGender'] = $request->gender;
        }

        if($request->filled('password')){
            $input['password'] = bcrypt($request->password);
            $input['vPassword'] = $request->password;
        }

        if($request->hasFile('profile_picture')){
            $fileName = storeFile('profile-pictures', $request->profile_picture);
            $input['vImage'] = $fileName;
        }

        $user = User::create($input);
        if ($user) {
            $role = Role::where(['name'=>config('constant.roles.admin')])->first();
            if ($role) {
                $user->assignRole($role);
            }
        }
        return redirect()->route('admins.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User $admin
     * @return \Illuminate\Http\Response
     */
    public function show(User $admin)
    {
        return view('users.admin.view', compact('admin'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User $admin
     * @return \Illuminate\Http\Response
     */
    public function edit(User $admin)
    {
        $countryData = MasterCountry::status()->get();
        return view('users.admin.edit', compact('admin', 'countryData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreAdminRequest  $request
     * @param  \App\Models\User  $admin
     * @return \Illuminate\Http\Response
     */
    public function update(StoreAdminRequest $request, User $admin)
    {
        $input = $request->all();

        $input['vFirstName'] = $request->first_name;
        $input['vLastName'] = $request->last_name;
        $input['vEmail'] = $request->email;
        $input['username'] = $request->username;
        $input['vOccupation'] = $request->profession;
        $input['vPhoneNumber'] = $request->contact_number;
        $input['whatsapp_number'] = $request->whatsapp_number;
        $input['tAboutMe'] = $request->about_me;
        $input['vSkill'] = $request->user_skills;
        $input['vExperience'] = $request->user_experience;
        $input['business_name'] = $request->business_name;
        $input['country_id'] = $request->country_id;
        $input['state_id'] = $request->state_id;
        $input['facebook_link'] = $request->facebook_link;
        $input['twitter_link'] = $request->twitter_link;
        $input['linkedin_link'] = $request->linkedin_link;
        $input['instagram_link'] = $request->instagram_link;
        $input['youtube_link'] = $request->youtube_link;
        $input['status'] = $request->status;

        $input['eGender'] = ($request->filled('gender')) 
            ? $request->gender 
            : $admin->eGender;
        
        if($request->filled('password')){
            $input['password'] = bcrypt($request->password);
            $input['vPassword'] = $request->password;
        } else {
            $input['password'] = $admin->password;
            $input['vPassword'] = $admin->vPassword;
        }

        if($request->hasFile('profile_picture')){
            if(fileExists($admin->profile_picture)) {
                deleteFile($admin->profile_picture);
            }

            $fileName = storeFile('profile-pictures', $request->profile_picture);
            $input['vImage'] = $fileName;
        }

        $admin->update($input);

        return redirect()->route('admins.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $admin
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $admin)
    {
        $admin->delete();
    }
    
    public function changeStatus(User $admin, Request $request)
    {
        $inputData = $request->all();

        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $admin->update(['status' => $status]);

        return true;
    }
}
