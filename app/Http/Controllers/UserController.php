<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use DataTables;

class UserController extends Controller
{
    /**
     * Show the user list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function adminIndex(Request $request)
    {
        return view('user.index');
    }

    public function userList(Request $request)
    {
        if ($request->ajax()) {

            $data = User::query();
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $actionBtn = '<a href="javascript:void(0)" class="edit btn btn-success btn-sm">Edit</a> <a href="javascript:void(0)" class="delete btn btn-danger btn-sm">Delete</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        return view('user.profile', [
            'user' => $user
        ]);
    }

    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function profileUpdate(Request $request, User $user)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->vFirstName = $request->first_name;
        $user->vLastName = $request->last_name;

        if(isset($request->password)){
            $user->password = bcrypt($request->password);
        }

        if($request->hasFile('profile_picture')){
            
            if(fileExists($user->vImage)) {
                deleteFile($user->vImage);
            }
            $fileName = storeFile('profile-pictures', $request->profile_picture);
            $user->vImage = $fileName;
        }

        $user->save();

        return redirect('admin/dashboard')->with('success', "Profile updated successfully");
    }
}
