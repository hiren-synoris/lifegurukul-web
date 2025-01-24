<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;
        $pathSegments = parse_url(url()->current(), PHP_URL_PATH);
        $explodedPath = explode('/', $pathSegments);
        $desiredSegment = $explodedPath[1] ?? null;
        $mobile = $explodedPath[2] ?? null;

        foreach ($guards as $guard) {
            if ($guard == "learner" && Auth::guard($guard)->check()) {
                if($desiredSegment=="direct-login") {
                    Auth::guard('learner')->logout();
                    return redirect('direct-login/'.$mobile.'?c_id='.$request->c_id);
                }
                return redirect('/');
            }

            if (Auth::guard($guard)->check()) {
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
