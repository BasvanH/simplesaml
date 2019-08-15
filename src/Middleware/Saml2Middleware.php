<?php

namespace BasvanH\SimpleSaml\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use BasvanH\SimpleSaml\Facades\Saml2Auth;

class Saml2Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guest())
		{
			if ($request->ajax())
			{
				return response('Unauthorized.', 401);
			}
			else
			{
        		return Saml2Auth::login(config('saml2_settings.loginRoute'));
			}
        }
        
        return $next($request);
    }
}
