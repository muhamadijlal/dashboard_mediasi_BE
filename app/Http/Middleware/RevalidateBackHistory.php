<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RevalidateBackHistory
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
        $response->headers->set('Cache-Control', 'no-cache, must-revalidate, no-store, max-age=0, private');

        return $response;
    }
}
