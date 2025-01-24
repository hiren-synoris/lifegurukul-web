<?php

namespace App\Http\Middleware;

use App\Models\DeviceToken;
use Closure;
use Illuminate\Http\Request;

class BlockDevice
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

        $checkBlockDevice = DeviceToken::where("block_device", 0)->where("device_id", $request->header('deviceid'))->first();
        if ($checkBlockDevice) {
            $token = $request->user()->token();
            $token->revoke();
            return response()->json(['message' => 'Your device is blocked, contact to support.'], 401);
        }

        return $next($request);
    }
}
