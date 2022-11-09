<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\MasterCountry;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgentRequest;
use App\Models\TempUser;

class AgentController extends Controller
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
                    $q->whereIn('name', [config('constant.roles.agent')]);
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
                    $btn = '<a href="'.route('agents.show',[$row->iUserId]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="'.route('agents.edit',[$row->iUserId]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->iUserId.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('users.agent.index_approved');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countryData = MasterCountry::status()->get();
        return view('users.agent.create_approved', compact('countryData'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAgentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAgentRequest $request)
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
        $input['vMyCode'] = generateReferralCode(12);
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

        $agent = User::create($input);
        if ($agent) {
            $role = Role::where(['name'=>config('constant.roles.agent')])->first();
            if ($role) {
                $agent->assignRole($role);
            }
        }
        return redirect()->route('agents.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User $agent
     * @return \Illuminate\Http\Response
     */
    public function show(User $agent)
    {
        return view('users.agent.view_approved', compact('agent'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User $agent
     * @return \Illuminate\Http\Response
     */
    public function edit(User $agent)
    {
        $countryData = MasterCountry::status()->get();
        return view('users.agent.edit_approved', compact('agent', 'countryData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreAgentRequest  $request
     * @param  \App\Models\User  $agent
     * @return \Illuminate\Http\Response
     */
    public function update(StoreAgentRequest $request, User $agent)
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
            : $agent->eGender;

        if($request->filled('password')){
            $input['password'] = bcrypt($request->password);
            $input['vPassword'] = $request->password;
        } else {
            $input['password'] = $agent->password;
            $input['vPassword'] = $agent->vPassword;
        }

        if($request->hasFile('profile_picture')){
            if(fileExists($agent->profile_picture)) {
                deleteFile($agent->profile_picture);
            }

            $fileName = storeFile('profile-pictures', $request->profile_picture);
            $input['vImage'] = $fileName;
        }

        $agent->update($input);

        return redirect()->route('agents.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $agent
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $agent)
    {
        $agent->delete();
    }
    
    public function changeStatus(User $agent, Request $request)
    {
        $inputData = $request->all();

        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $agent->update(['status' => $status]);

        return true;
    }

    public function indexInvited(Request $request)
    {
        if ($request->ajax()) {
            $data = TempUser::query();
            $data = $data->where('user_role', config('constant.roles.agent'))
            ->where(function ($query) {
                $query->where('user_status', config('constant.invitation.reject'))
                    ->orWhereNull('user_status');
            })->select('id', 'first_name', 'last_name', 'email', 'contact_number', 'whatsapp_number', 'status')->orderByDesc('id');
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('agents.invited.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    if($row->user_status == config('constant.invitation.reject')) {
                        $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 btn btn-dark btn-xs" title="Rejection Note: '.$row->user_note.'"><i class="fa fa-info-circle"></i></a>';
                    } else {
                        $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 approveBtn btn btn-success btn-xs" title="Approve"><i class="fa fa-check"></i></a>';
                        $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 rejectBtn btn btn-warning btn-xs" title="Reject"><i class="fa fa-times"></i></a>';
                    }
                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete Invitation"><i class="fa fa-trash"></i></a>';

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('users.agent.index_invited');
    }

    public function showInvited(TempUser $agent)
    {
        return view('users.agent.view_invited', compact('agent'));
    }

    public function destroyInvited(TempUser $agent)
    {
        $agent->delete();
    }

    public function changeInvitationStatus(TempUser $agent, Request $request)
    {
        $inputData = $request->all();
        $userStatus = $inputData['user_status'];

        //reject button
        if($userStatus == config('constant.invitation.reject')) {
            $agent->update($inputData);
        } else {
            $userId = $agent->user_id;

            $user = User::find($userId);

            if($user && $userStatus == config('constant.invitation.approve')) {
                if($agent->username) {
                    $user->username = $agent->username;
                }

                if($agent->gender) {
                    $user->eGender = $agent->gender;
                }

                if($agent->profession) {
                    $user->vOccupation = $agent->profession;
                }

                if($agent->about_me) {
                    $user->tAboutMe = $agent->about_me;
                }

                if($agent->user_skills) {
                    $user->vSkill = $agent->user_skills;
                }

                if($agent->user_experience) {
                    $user->vExperience = $agent->user_experience;
                }

                if($agent->business_name) {
                    $user->business_name = $agent->business_name;
                }

                if($agent->facebook_link) {
                    $user->facebook_link = $agent->facebook_link;
                }

                if($agent->twitter_link) {
                    $user->twitter_link = $agent->twitter_link;
                }

                if($agent->linkedin_link) {
                    $user->linkedin_link = $agent->linkedin_link;
                }

                if($agent->instagram_link) {
                    $user->instagram_link = $agent->instagram_link;
                }

                if($agent->youtube_link) {
                    $user->youtube_link = $agent->youtube_link;
                }

                if($agent->latitude) {
                    $user->latitude = $agent->latitude;
                }

                if($agent->longitude) {
                    $user->longitude = $agent->longitude;
                }

                if($agent->country_id) {
                    $user->country_id = $agent->country_id;
                }

                if($agent->state_id) {
                    $user->state_id = $agent->state_id;
                }

                if($agent->district_id) {
                    $user->district_id = $agent->district_id;
                }

                if($agent->taluka_id) {
                    $user->taluka_id = $agent->taluka_id;
                }

                if($agent->refer_code) {
                    $user->vReferCode = $agent->refer_code;
                }

                if($agent->refer_user_id) {
                    $user->iReferUserId = $agent->refer_user_id;
                    $user->vTeamId = $agent->team_id != "" ? $agent->team_id."-".$agent->refer_user_id : $agent->refer_user_id;
                }

                $user->save();
                
                $role = Role::where(['name'=>config('constant.roles.agent')])->first();
                if ($role) {
                    $user->assignRole($role);
                }
                
                $agent->forceDelete();
            }
        }

        return true;
    }
}
