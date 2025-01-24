<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class AuthOptional
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        // $authData = auth()->user()->id;
        // $currentToken = request()->bearerToken();

        // $token = "35b08dd4e122aec3d6a24946c4ef0c6890d7abac972307904f4e32c3f6176539a357ae703d71b3d6";
        // if( $currentToken !== $token){
        //     return response()->json(["status" => 0 , "message" => "Unauthorized"], 401); // 401 being the HTTP code for an unauthenticated request.
        // }
        return $next($request);
        // $tokensMatch = strcmp($currentToken, $token) ? true : false;
    }

    

}