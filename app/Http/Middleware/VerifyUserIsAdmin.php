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
			// Make sure that the user is authenticated
			// If they aren't this will throw an error 
			parent::handle($request, $next, $guards); 

			if($request->user()->isAdmin){
			  return $next($request);
			} else {
				abort(403);
			}
    }
}
