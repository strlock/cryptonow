<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Test{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next){
        if(preg_match('/^(109|172)\./', $request->server('REMOTE_ADDR'))){
            abort(403, 'Blocked by IP');
        }
        return $next($request);
    }
}
