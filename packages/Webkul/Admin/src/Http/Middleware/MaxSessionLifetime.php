<?php

namespace Webkul\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaxSessionLifetime
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->guard('user')->check()) {
            $loggedInAt = session('logged_in_at', now()->timestamp);

            if (now()->timestamp - $loggedInAt > 28800) {
                session()->forget('two_factor_authenticated_at');
                session()->forget('logged_in_at');
                session()->forget('last_active_at');

                auth()->guard('user')->logout();

                session()->flash('error', trans('admin::app.users.session-expired'));

                return redirect()->route('admin.session.create');
            }
        }

        return $next($request);
    }
}
