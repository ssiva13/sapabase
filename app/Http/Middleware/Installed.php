<?php

namespace Acelle\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Installed
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (isInitiated()) {
            return redirect()->action('HomeController@index');
        }

        return $next($request);
    }
}
