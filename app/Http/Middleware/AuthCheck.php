<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthCheck
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
        /* if(!session()->has('LoggedUser'))
        {
            return redirect('entry-point');
        }
        
        return $next($request); */

        if(!session()->has('LoggedUser') && $request->path() != '/')
        {
            return redirect('/')->with('fail','Login to Proceed');
        }

        if(session()->has('LoggedUser') && $request->path() == '/')
        {
            if(session('LoggedUserType') == '')
            {
                return back();
            }
            else
            {
                if(session('LoggedUserType') == 1)
                {
                    //return redirect('dashboard');
                    return redirect('brand-cvs');
                } 
                else
                {
                    return redirect('welcome'); 
                }
            }
        }
        
        return $next($request)->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                              ->header('Pragma', 'no-cache')
                              ->header('Expires', 'Sat 01 Jan 1990 00:00:00 GMT');
    }
}
