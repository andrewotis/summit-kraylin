<?php

namespace Webkul\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequiresTwoFactor
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->guard('user')->user();

        if (
            $user
            && $user->google2fa_secret
            && $user->two_factor_confirmed_at
            && ! session()->has('two_factor_authenticated_at')
        ) {
            return redirect()->route('admin.two-factor.challenge');
        }

        return $next($request);
    }
}
