<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            JWTAuth::parseToken()->authenticate();
            if ($request->user()) {
                $response = $next($request);
                return $response;
            }
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            Log::error($e);
        }

        return response()->json([
            'message'   => "You are not authorized to access this resource.",
            'success'   => false,
            'status'    => false,
            'data'      => null
        ], 401);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('login');
        }

        return null;
    }
}
