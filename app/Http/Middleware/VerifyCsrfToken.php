<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    public function handle($request, Closure $next)
    {
        // Check if session is expired
        // if ($request->session()->has('lastActivity')) {
        //     $lastActivity = $request->session()->get('lastActivity');
        //     $sessionTimeout = config('session.lifetime') * 60; // Convert minutes to seconds
        //     if (time() - $lastActivity > $sessionTimeout) {
        //         // Session expired, handle as needed (e.g., return a response, redirect, etc.)
        //         return response()->json(['message' => 'Session expired'], 419);
        //     }
        // }

        // Update last activity time
        $request->session()->put('lastActivity', time());

        return parent::handle($request, $next);
    }
}
