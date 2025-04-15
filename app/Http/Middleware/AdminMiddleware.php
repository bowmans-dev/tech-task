<?php

namespace App\Http\Middleware;

use Closure;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        // Check if the current user is authenticated via the admin guard
        if (! auth()->guard('admin')->check()) {
            // Return JSON response for API requests
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Redirect for web-based requests
            return redirect()->route('login')->with('error', 'You must log in as an admin to access this page.');
        }

        return $next($request); // Allow request to proceed
    }
}
