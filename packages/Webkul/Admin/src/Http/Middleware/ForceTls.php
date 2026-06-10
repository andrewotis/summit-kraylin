<?php

namespace Webkul\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceTls
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isSecure() && ! app()->environment('local', 'testing')) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
