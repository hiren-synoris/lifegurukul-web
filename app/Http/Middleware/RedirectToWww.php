<?php

namespace App\Http\Middleware;

use Closure;

class RedirectToWww
{
    public function handle($request, Closure $next)
    {
        if (!$request->is('www.*')) {
            // dd("sdfsdf");  
            return redirect()->to('https://www.' . $request->getHttpHost() . $request->getRequestUri(), 301);
        }

        return $next($request);
    }
}