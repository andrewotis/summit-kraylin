<?php

namespace Webkul\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IdleTimeout
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->guard('user')->check()) {
            $lastActivity = session('last_active_at', now()->timestamp);

            if (now()->timestamp - $lastActivity > 900) {
                session()->forget('two_factor_authenticated_at');
                session()->forget('last_active_at');

                auth()->guard('user')->logout();

                session()->flash('error', trans('admin::app.users.idle-timeout'));

                return redirect()->route('admin.session.create');
            }

            session(['last_active_at' => now()->timestamp]);
        }

        return $next($request);
    }
}
