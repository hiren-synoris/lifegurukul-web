<?php

namespace App\Http\Controllers\admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // if (!$this->user->can('edit_users') && !$this->user->can('edit_learners') && !$this->user->can('edit_instructors') && !$this->user->can('edit_subadmin')) abort(403);
        $user = User::findOrFail($id);
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $notification = [];
        $user = User::findOrFail($id);
        // if (!$this->user->can('edit_users') && !$this->user->can('edit_learners') && !$this->user->can('edit_instructors') && !$this->user->can('edit_subadmin')) abort(403);

        $validated = $request->validate([
            'name' => 'required|filled|max:255|regex:/^[a-zA-ZÑñ\s]+$/',
            'email' => ['required','filled','regex:/(.+)@(.+)\.(.+)/i','email',Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'sometimes|min:8|nullable|max:16',
            'profile_picture' => 'sometimes|mimes:jpeg,jpg,png|present|max:500'
        ]);

        $file_path = $user->profile_picture;
        if(request()->hasFile('profile_picture')){
            if(!empty($user->profile_picture)){
                Storage::delete($user->profile_picture);
            }
            $file_path = Storage::putFileAs('profile_pic', $request->profile_picture, $user->id.'_'.$request->profile_picture->getClientOriginalName());
        }

        if ($request->has('password') && !empty($request->password)) {
            $user->password = bcrypt($request->password);
        }
        else{
            $user->password = $request->old_password;
        }

        User::where('id', $user->id)->update([
            'name' => request()->has('name') ? $request->name : '',
            'email' => request()->has('email') ? $request->email : '',
            'password' => $user->password,
            'profile_picture' => $file_path,
            'updated_by' => auth()->id()
        ]);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Profile updated successfully";
        return redirect()->route('dashboard')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
