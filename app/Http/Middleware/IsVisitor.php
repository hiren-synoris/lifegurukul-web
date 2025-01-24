<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsVisitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::guard('learner-api')->check()==true) {

            $user =  Auth::guard('learner-api')->user();
//dd(            $user);
            $request->request->add(['scope' => 'learner']); //add request
            $request->request->add(['user_id' => $user->id]); //add request
            $request->request->add(['learner_email' => $user->email]);
        }

        $deviceType = $request->header('devicetype');
        $device_price_field = ($deviceType == 1 ? 'default_android_price' : ($deviceType == 2 ? 'default_iphone_price' : ''));
        $request->request->add(['deviceid' => $request->header('deviceid')]); //add request
        $request->request->add(['devicetype' => $request->header('devicetype')]); //add request
        $request->request->add(['devicetoken' => $request->header('devicetoken')]); //add request
        $request->request->add(['device_price_field' => $device_price_field]); //add request

        return $next($request);
    }
}
