<?php

namespace App\Http\Middleware;

use \Illuminate\Auth\Middleware\Authenticate;
use Closure;

class VerifyUserIsAdmin extends Authenticate
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
			if($request->user() && $request->user()->isAdmin){
			  return $next($request);
			} else {
				abort(403);
			}
    }
}
