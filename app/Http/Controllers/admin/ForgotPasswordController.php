<?php

namespace App\Http\Controllers\admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\AdminForgotPassword;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

class ForgotPasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(view()->exists('admin.auth.passwords.forgot-password')){
            return view('admin.auth.passwords.forgot-password');
        } abort(404);
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


        $notification = [];
        $request->validate([
            'email' => 'required|present|filled|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();
        if(!$user) {
            abort(404);
        }
        $pin = rand(100000, 999999);
        $user->remember_token = $pin;
        $user->save();
        Mail::to($request->email)->send(new AdminForgotPassword($user));

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Email Send successfully";

        return redirect()->back()->with('notification', $notification)->with('success', 'Email sent successfully');
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
        $id = Crypt::decrypt($id);
        $user = User::where('id', $id)->first();
        if($user->remember_token){
            return view('admin.auth.passwords.reset-password', compact('id'));
        }else{
            abort(403);
        }

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
        \Validator::extend('without_spaces', function($attr, $value){
            return preg_match('/^\S*$/u', $value);
        });


        $request->validate([
            // 'password' => 'required|present|filled|confirmed|min:8|max:16'
            'password' => 'required|min:8|present|filled|without_spaces|confirmed|max:16|filled|regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#>])[A-Za-z\d@$!%*?&#>]+$/',
            'password_confirmation' => 'required|required_with:password|same:password'
        ],[
            "password.without_spaces" => "The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",
            "password.regex"=>"The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",
            "password.required" => "The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",
            "password.min" => "The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",
            "password.max" => "The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",

        ]);

        User::where('id', $id)->update([
            'password' => bcrypt($request->password),
            'remember_token' => ''
        ]);
        return redirect('backoffice/login')->with('success', 'Password updated successfully');
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
