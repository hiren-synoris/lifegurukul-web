<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WebLearnerMiddleware
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

        if (!auth()->guard("learner")->check()) {
            session(['url.intended' => $request->url()]);
            return redirect('/');
        }
        @preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $request->name);
        return $next($request);
    }
}
