<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\MasterCountry;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCreatorRequest;
use App\Models\TempUser;

class CreatorController extends Controller
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
                    $q->whereIn('name', [config('constant.roles.creator')]);
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
                    $btn = '<a href="'.route('creators.show',[$row->iUserId]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="'.route('creators.edit',[$row->iUserId]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->iUserId.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';

                    $btn .= '<a href="'.route('creators.posts',[$row->iUserId]).'" class="ml-1 demoPostBtn btn btn-secondary btn-xs" title="Posts"><i class="fa fa-video"></i></a>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('users.creator.index_approved');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countryData = MasterCountry::status()->get();
        return view('users.creator.create_approved', compact('countryData'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCreatorRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCreatorRequest $request)
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

        $creator = User::create($input);
        if ($creator) {
            $role = Role::where(['name'=>config('constant.roles.creator')])->first();
            if ($role) {
                $creator->assignRole($role);
            }
        }
        return redirect()->route('creators.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User $creator
     * @return \Illuminate\Http\Response
     */
    public function show(User $creator)
    {
        return view('users.creator.view_approved', compact('creator'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User $creator
     * @return \Illuminate\Http\Response
     */
    public function edit(User $creator)
    {
        $countryData = MasterCountry::status()->get();
        return view('users.creator.edit_approved', compact('creator', 'countryData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreCreatorRequest  $request
     * @param  \App\Models\User  $creator
     * @return \Illuminate\Http\Response
     */
    public function update(StoreCreatorRequest $request, User $creator)
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
            : $creator->eGender;
        
        if($request->filled('password')){
            $input['password'] = bcrypt($request->password);
            $input['vPassword'] = $request->password;
        } else {
            $input['password'] = $creator->password;
            $input['vPassword'] = $creator->vPassword;
        }

        if($request->hasFile('profile_picture')){
            if(fileExists($creator->profile_picture)) {
                deleteFile($creator->profile_picture);
            }

            $fileName = storeFile('profile-pictures', $request->profile_picture);
            $input['vImage'] = $fileName;
        }

        $creator->update($input);

        return redirect()->route('creators.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $creator
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $creator)
    {
        $creator->delete();
    }
    
    public function changeStatus(User $creator, Request $request)
    {
        $inputData = $request->all();

        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $creator->update(['status' => $status]);

        return true;
    }

    public function getPosts(User $creator, Request $request)
    {
        if ($request->ajax()) {
            $data = $creator->posts();
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('status', function($row){
                    if($row->status == config('constant.status.processing_value')){
                        $colCls = 'warning';
                    } else {
                        $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    }
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('posts.show',[$row->id,'view=creator_approved&user_id='.$row->user_id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';

                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';
                   
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('users.creator.posts_approved', compact('creator'));
    }

    public function indexInvited(Request $request)
    {
        if ($request->ajax()) {
            $data = TempUser::query();
            $data = $data->where('user_role', config('constant.roles.creator'))
            ->where(function ($query) {
                $query->where('user_status', config('constant.invitation.reject'))
                    ->orWhereNull('user_status');
            })->select('id', 'first_name', 'last_name', 'email', 'contact_number', 'whatsapp_number', 'status')->orderByDesc('id');
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('creators.invited.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    if($row->user_status == config('constant.invitation.reject')) {
                        $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 btn btn-dark btn-xs" title="Rejection Note: '.$row->user_note.'"><i class="fa fa-info-circle"></i></a>';
                    } else {
                        $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 approveBtn btn btn-success btn-xs" title="Approve"><i class="fa fa-check"></i></a>';
                        $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 rejectBtn btn btn-warning btn-xs" title="Reject"><i class="fa fa-times"></i></a>';
                    }
                    $btn .= '<a href="'.route('creators.invited.posts',[$row->id]).'" class="ml-1 demoPostBtn btn btn-secondary btn-xs" title="Demo Posts"><i class="fa fa-video"></i></a>';

                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete Invitation"><i class="fa fa-trash"></i></a>';

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('users.creator.index_invited');
    }

    public function showInvited(TempUser $creator)
    {
        return view('users.creator.view_invited', compact('creator'));
    }

    public function destroyInvited(TempUser $creator)
    {
        $creator->delete();
    }

    public function changeInvitationStatus(TempUser $creator, Request $request)
    {
        $inputData = $request->all();
        $userStatus = $inputData['user_status'];

        //reject button
        if($userStatus == config('constant.invitation.reject')) {
            $creator->update($inputData);
        } else {
            $userId = $creator->user_id;

            $user = User::find($userId);

            if($user && $userStatus == config('constant.invitation.approve')) {
                if($creator->username) {
                    $user->username = $creator->username;
                }

                if($creator->gender) {
                    $user->eGender = $creator->gender;
                }

                if($creator->profession) {
                    $user->vOccupation = $creator->profession;
                }

                if($creator->about_me) {
                    $user->tAboutMe = $creator->about_me;
                }

                if($creator->user_skills) {
                    $user->vSkill = $creator->user_skills;
                }

                if($creator->user_experience) {
                    $user->vExperience = $creator->user_experience;
                }

                if($creator->business_name) {
                    $user->business_name = $creator->business_name;
                }

                if($creator->facebook_link) {
                    $user->facebook_link = $creator->facebook_link;
                }

                if($creator->twitter_link) {
                    $user->twitter_link = $creator->twitter_link;
                }

                if($creator->linkedin_link) {
                    $user->linkedin_link = $creator->linkedin_link;
                }

                if($creator->instagram_link) {
                    $user->instagram_link = $creator->instagram_link;
                }

                if($creator->youtube_link) {
                    $user->youtube_link = $creator->youtube_link;
                }

                if($creator->latitude) {
                    $user->latitude = $creator->latitude;
                }

                if($creator->longitude) {
                    $user->longitude = $creator->longitude;
                }

                if($creator->country_id) {
                    $user->country_id = $creator->country_id;
                }

                if($creator->state_id) {
                    $user->state_id = $creator->state_id;
                }

                if($creator->district_id) {
                    $user->district_id = $creator->district_id;
                }

                if($creator->taluka_id) {
                    $user->taluka_id = $creator->taluka_id;
                }

                if($creator->refer_code) {
                    $user->vReferCode = $creator->refer_code;
                }

                if($creator->refer_user_id) {
                    $user->iReferUserId = $creator->refer_user_id;
                    $user->vTeamId = $creator->team_id != "" ? $creator->team_id."-".$creator->refer_user_id : $creator->refer_user_id;
                }

                $user->save();
                
                $role = Role::where(['name'=>config('constant.roles.creator')])->first();
                if ($role) {
                    $user->assignRole($role);
                }
                
                $creator->forceDelete();
            }
        }

        return true;
    }

    public function getInvitedPosts(TempUser $creator, Request $request)
    {
        if ($request->ajax()) {
            $data = $creator->posts();
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('status', function($row){
                    if($row->status == config('constant.status.processing_value')){
                        $colCls = 'warning';
                    } else {
                        $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    }
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row) use($creator){
                    $btn = '<a href="'.route('posts.show',[$row->id,'view=creator_invited&user_id='.$creator->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';

                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';
                   
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('users.creator.posts_invited', compact('creator'));
    }
}
