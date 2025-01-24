<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BrowserSuport
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
        $user_agent = $request->header('User-Agent');

        if($request->header()){

            $device_name="";
            $ua = $request->header('user-agent');
            $ua_exp = explode(" ", $request->header('user-agent'));
            
            if(strpos($ua, 'Android') == true) {
                if(str_contains($ua,'Edg/')){
                    $device_name = str_replace("/"," ",isset($ua_exp[10]))." ".isset($ua_exp[2]);
                    $device_name = str_replace("Edg", "Edge", $device_name);
                }
                else if(str_contains($ua,'Firefox/')){
                    $device_name = str_replace("/"," ",isset($ua_exp[6]))." ".rtrim(isset($ua_exp[1]), ";");
                }
                else if(str_contains($ua,'Chrome/')){
                    $device_name = str_replace("/"," ",isset($ua_exp[9]))." ".isset($ua_exp[2]);

                }
            }
            else if(strpos($ua, 'iPhone') == true) {
                if(str_contains($ua,'Safari/') && !str_contains($ua,'Chrome/')){

                    // dd(explode("/",$ua_exp[14])[1]);
                    // dd(explode("/",$ua_exp[16])[0]);
                    if(explode("/",$ua_exp[16])[0]=="Safari") {
                        $device_name = isset(explode("/",$ua_exp[16])[0])." ".isset(explode("/",$ua_exp[14])[1])." "."iOS";
                    } else {
                        $device_name = "Chrome"." ".explode("/",$ua_exp[14])[1]." "."iOS";
                    }
                    // dd($ua_exp);
                }
                else if(str_contains($ua,'Edg/')){
                    $device_name = str_replace("/"," ",isset($ua_exp[13]))." ".isset($ua_exp[4])." ".isset($ua_exp[5]);
                    $device_name = str_replace("Edg", "Edge", $device_name);
                }
                else if(str_contains($ua,'Chrome/')){
                    
                    $device_name = str_replace("/"," ",isset($ua_exp[11]))." ".isset($ua_exp[4])." ".isset($ua_exp[5]);
                }
                else if(str_contains($ua,'Firefox/')){
                    $device_name = str_replace("/"," ",isset($ua_exp[9]))." ".isset($ua_exp[4])." ".isset($ua_exp[5]);
                }
            }
            else if(str_contains($ua, "Macintosh")){

                if(str_contains($ua,'Safari/') && !str_contains($ua,'Chrome/')){
                    $device_name = isset(explode("/",$ua_exp[12])[0])." ".isset(explode("/",$ua_exp[11])[1])." ".isset($ua_exp[4])." ".isset($ua_exp[5]);
                }
                else if(str_contains($ua,'Edg/')){
                    $device_name = str_replace("/"," ",isset($ua_exp[13]))." ".isset($ua_exp[4])." ".isset($ua_exp[5]);
                    $device_name = str_replace("Edg", "Edge", $device_name);
                }
                else if(str_contains($ua,'Chrome/')){
                    $device_name = str_replace("/"," ",isset($ua_exp[11]))." ".isset($ua_exp[4])." ".isset($ua_exp[5]);
                }
                else if(str_contains($ua,'Firefox/')){
                    $device_name = str_replace("/"," ",isset($ua_exp[9]))." ".isset($ua_exp[4])." ".isset($ua_exp[5]);
                }
            }
            else if(str_contains($ua, "Linux")){
                if(str_contains($ua,'Edg/')){
                    $device_name = str_replace("/"," ",isset($ua_exp[10]))." ".isset($ua_exp[2]);
                    $device_name = str_replace("Edg", "Edge", $device_name);
                }
                else if(str_contains($ua,'Firefox/')){
                    $device_name = str_replace("/"," ",isset($ua_exp[7]))." ".rtrim(isset($ua_exp[2]), ";");

                }
                else if(str_contains($ua,'Chrome/')){
                    $device_name = str_replace("/"," ",isset($ua_exp[8]))." ".isset($ua_exp[2]);
                }
            }
            else{
                
                if(str_contains($ua,'Edg/')){
                   
                    $device_name = str_replace("/"," ",isset($ua_exp[12]))." ".ltrim(isset($ua_exp[1]), "(");
                    $device_name = str_replace("Edg", "Edge", $device_name);
                }
                else if(str_contains($ua,'Firefox/')){
                    $device_name = str_replace("/"," ",isset($ua_exp[8]))." ".ltrim(isset($ua_exp[1]), "(");
                }
                else if(str_contains($ua,'Chrome/')){
                    $device_name = str_replace("/"," ",isset($ua_exp[100]))." ".ltrim(isset($ua_exp[100]), "(");

                }
            }

            if(empty($device_name)){
                abort(420);
            }

        }

        return $next($request);
    }
}
