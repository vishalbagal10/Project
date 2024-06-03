<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HttpRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // check if environment is production
        if(env('APP_ENV') === "production") {
            if (!$request->secure()) {
                return redirect()->secure($request->path());
            }
        }
        return $next($request);
    }
}
