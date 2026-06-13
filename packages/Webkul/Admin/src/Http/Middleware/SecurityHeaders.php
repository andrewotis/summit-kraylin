<?php

namespace Webkul\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $csp = "default-src 'self'; "
            ."script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://npmcdn.com https://cdnjs.cloudflare.com; "
            ."style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://npmcdn.com https://unpkg.com https://cdnjs.cloudflare.com; "
            ."font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com; "
            ."img-src 'self' data: https://chart.googleapis.com https://cdnjs.cloudflare.com; "
            ."connect-src 'self'; "
            ."frame-ancestors 'none'; "
            ."form-action 'self'; "
            ."base-uri 'self'";

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
