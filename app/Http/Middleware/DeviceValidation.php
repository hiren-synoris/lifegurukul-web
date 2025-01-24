<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Facades\Agent;

class DeviceValidation
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
        if(Auth::guard('learner')->check()){
            if($request->header()){
                $learner_id = auth()->guard('learner')->id();
                $device_name="";
                $ua = $request->header('user-agent');
                // dd($ua);
                $ua_exp = explode(" ", $request->header('user-agent'));

                if(strpos($ua, 'Android') == true) {
                    if(str_contains($ua,'Edg/')){
                        $device_name = str_replace("/"," ",$ua_exp[10])." ".$ua_exp[2];
                        $device_name = str_replace("Edg", "Edge", $device_name);
                    }
                    else if(str_contains($ua,'Firefox/')){
                        $device_name = str_replace("/"," ",$ua_exp[6])." ".rtrim($ua_exp[1], ";");
                    }
                    else if(str_contains($ua,'Chrome/')){
                        $device_name = str_replace("/"," ",$ua_exp[9])." ".$ua_exp[2];

                    }

                }
                else if(strpos($ua, 'iPhone') == true) {
                    if(str_contains($ua,'Safari/') && !str_contains($ua,'Chrome/')){

                        // dd(explode("/",$ua_exp[14])[1]);
                        // dd(explode("/",$ua_exp[16])[0]);
                        if(explode("/",$ua_exp[16])[0]=="Safari") {
                            $device_name = explode("/",$ua_exp[16])[0]." ".explode("/",$ua_exp[14])[1]." "."iOS";
                        } else {
                            $device_name = "Chrome"." ".explode("/",$ua_exp[14])[1]." "."iOS";
                        }
                        // dd($ua_exp);
                    }
                    else if(str_contains($ua,'Edg/')){
                        $device_name = str_replace("/"," ",$ua_exp[13])." ".$ua_exp[4]." ".$ua_exp[5];
                        $device_name = str_replace("Edg", "Edge", $device_name);
                    }
                    else if(str_contains($ua,'Chrome/')){

                        $device_name = str_replace("/"," ",$ua_exp[11])." ".$ua_exp[4]." ".$ua_exp[5];
                    }
                    else if(str_contains($ua,'Firefox/')){
                        $device_name = str_replace("/"," ",$ua_exp[9])." ".$ua_exp[4]." ".$ua_exp[5];
                    }
                }

                else if(str_contains($ua, "Macintosh")){

                    if(str_contains($ua,'Safari/') && !str_contains($ua,'Chrome/')){
                        $device_name = explode("/",$ua_exp[12])[0]." ".explode("/",$ua_exp[11])[1]." ".$ua_exp[4]." ".$ua_exp[5];
                    }
                    else if(str_contains($ua,'Edg/')){
                        $device_name = str_replace("/"," ",$ua_exp[13])." ".$ua_exp[4]." ".$ua_exp[5];
                        $device_name = str_replace("Edg", "Edge", $device_name);
                    }
                    else if(str_contains($ua,'Chrome/')){
                        $device_name = str_replace("/"," ",$ua_exp[11])." ".$ua_exp[4]." ".$ua_exp[5];
                    }
                    else if(str_contains($ua,'Firefox/')){
                        $device_name = str_replace("/"," ",$ua_exp[9])." ".$ua_exp[4]." ".$ua_exp[5];
                    }
                }
                else if(str_contains($ua, "Linux")){
                    if(str_contains($ua,'Edg/')){
                        $device_name = str_replace("/"," ",$ua_exp[10])." ".$ua_exp[2];
                        $device_name = str_replace("Edg", "Edge", $device_name);
                    }
                    else if(str_contains($ua,'Firefox/')){
                        $device_name = str_replace("/"," ",$ua_exp[7])." ".rtrim($ua_exp[2], ";");

                    }
                    else if(str_contains($ua,'Chrome/')){
                        $device_name = str_replace("/"," ",$ua_exp[8])." ".$ua_exp[2];
                    }
                }
                else{
                    if(str_contains($ua,'Edg/')){
                        $device_name = str_replace("/"," ",$ua_exp[12])." ".ltrim($ua_exp[1], "(");
                        $device_name = str_replace("Edg", "Edge", $device_name);
                    }
                    else if(str_contains($ua,'Firefox/')){
                        $device_name = str_replace("/"," ",$ua_exp[8])." ".ltrim($ua_exp[1], "(");
                    }
                    else if(str_contains($ua,'Chrome/')){
                        $device_name = str_replace("/"," ",$ua_exp[10])." ".ltrim($ua_exp[1], "(");

                    }
                }
                // dd($device_name);
                if(!empty($device_name)){
                    // $dt = DeviceToken::select("id")->where('learner_id', $learner_id)->where('device_name', $device_name)->get();
                    $dt = DeviceToken::select("device_name")->where('learner_id', $learner_id)->get();
                    // dd($dt->toArray());
                    if($dt->isEmpty()){
                        $request->session()->invalidate();
                        Auth::logout();
                        return redirect($request->getRequestUri());
                    }
                }
                else{
                    $request->session()->invalidate();
                    Auth::logout();
                    return redirect('/');
                }
            }
        }
        return $next($request);
    }
}
