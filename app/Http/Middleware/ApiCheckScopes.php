<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Laravel\Passport\Exceptions\MissingScopeException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Helper\Helper;

class ApiCheckScopes
{
    public $attributes;
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$scopes
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\AuthenticationException|\Laravel\Passport\Exceptions\MissingScopeException
     */
    public function handle($request, $next, ...$scopes)
    {

        $header_data = $request->header();
        $validator_header_data = Validator::make($header_data, [
            'deviceid' => 'required|filled',
            'devicetype' => 'required|filled',
            'devicetoken' => 'required|filled'
        ]);

        if ($validator_header_data->fails()) {
            $data = Helper::apiResonse(0, $validator_header_data->messages(), []);
            return response()->json($data, 400);
        } else if($request->header('devicetype') != 1 && $request->header('devicetype') != 2) {
            $data = Helper::apiResonse(0, 'Devicetype should be 1 or 2 ', []);
            return response()->json($data, 400);
        }


        if (!$request->user() || !$request->user()->token()) {
            throw new AuthenticationException;
        }
        foreach ($scopes as $scope) {
            if ($request->user()->tokenCan($scope)) {
                if ($scope == 'learner') {
                    $user =  Auth::guard('learner-api')->user();
                    $request->request->add(['scope' => 'learner']); //add request
                    $request->request->add(['user_id' => $user->id]); //add request
                    $request->request->add(['learner_email' => $user->email]); //add request

                }
                //  else {
                //     $user = Auth::guard('visitor-api')->user();
                //     $request->request->add(['scope' => 'visitor']); //add request
                //     $request->request->add(['user_id' => $user->id]); //add request
                // }
                $deviceType = $request->header('devicetype');
                $device_price_field = ($deviceType == 1 ? 'default_android_price' : ($deviceType == 2 ? 'default_iphone_price' : ''));
                $request->request->add(['deviceid' => $request->header('deviceid')]); //add request
                $request->request->add(['devicetype' => $request->header('devicetype')]); //add request
                $request->request->add(['devicetoken' => $request->header('devicetoken')]); //add request
                $request->request->add(['device_price_field' => $device_price_field]); //add request

                return $next($request);
            }
        }
        // return $next($request);
        return response()->json(['status' => 0, 'errors' => 'This type of user cannot do this action.'], 400);
    }
}
