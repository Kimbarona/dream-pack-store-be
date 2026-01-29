<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin
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
        // Check if the request is already for the admin panel login page
        $isAlreadyLoginPage = $request->is('admin/login') || $request->is('filament.admin.auth.login');
        
        // Check if this is not the initial request (to avoid redirect loops)
        $isInitialRequest = !$request->headers->has('referer');
        
        // Only redirect if the request is for admin panel and user is not authenticated,
        // and the user is not already on the login page, and this is not the initial request
        if (($request->is('admin/*') || $request->is('admin')) && 
            !$isAlreadyLoginPage && 
            !Auth::guard('admin')->check() && 
            $isInitialRequest) {
            
            return redirect()->route('filament.admin.auth.login');
        }

        return $next($request);
    }
}