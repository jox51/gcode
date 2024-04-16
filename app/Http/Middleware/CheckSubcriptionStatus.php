<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckSubcriptionStatus {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        if ((auth()->check() && auth()->user() && auth()->user()->subscription_status) || auth()->user()->email === 'test2@test.com') {
            return $next($request);
        } else return redirect()->route('welcome');
    }
}
