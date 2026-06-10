<?php

namespace Webkul\Admin\Http\Controllers\User;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Menu\MenuItem;
use Webkul\Core\Models\AuditLog;

class SessionController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(): RedirectResponse|View
    {
        if (auth()->guard('user')->check()) {
            return redirect()->route('admin.dashboard.index');
        }

        $previousUrl = url()->previous();

        $intendedUrl = str_contains($previousUrl, 'admin')
            ? $previousUrl
            : route('admin.dashboard.index');

        session()->put('url.intended', $intendedUrl);

        return view('admin::sessions.login');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): RedirectResponse
    {
        $this->validate(request(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $lockoutKey = 'login-lockout:'.sha1(request()->ip().'|'.request('email'));

        $lockoutData = Cache::get($lockoutKey, ['attempts' => 0, 'locked_until' => null]);

        if (
            $lockoutData['attempts'] >= 5
            && $lockoutData['locked_until']
            && now()->lt($lockoutData['locked_until'])
        ) {
            $minutes = (int) ceil(now()->diffInMinutes($lockoutData['locked_until']));

            session()->flash('error', trans('admin::app.users.login-error-lockout', ['minutes' => $minutes]));

            return redirect()->back();
        }

        if (
            $lockoutData['locked_until']
            && now()->gte($lockoutData['locked_until'])
        ) {
            $lockoutData = ['attempts' => 0, 'locked_until' => null];
        }

        if (! auth()->guard('user')->attempt(request(['email', 'password']), request('remember'))) {
            $lockoutData['attempts']++;

            if ($lockoutData['attempts'] >= 5) {
                $lockoutData['locked_until'] = now()->addMinutes(15);
            }

            Cache::put($lockoutKey, $lockoutData, 900);

            AuditLog::create([
                'user_id' => null,
                'action' => 'login_failed',
                'entity_type' => 'users',
                'entity_id' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'payload' => ['email' => request('email')],
            ]);

            session()->flash('error', trans('admin::app.users.login-error'));

            return redirect()->back();
        }

        Cache::forget($lockoutKey);

        session(['logged_in_at' => now()->timestamp]);

        AuditLog::create([
            'user_id' => auth()->guard('user')->id(),
            'action' => 'login',
            'entity_type' => 'users',
            'entity_id' => auth()->guard('user')->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'payload' => null,
        ]);

        if (auth()->guard('user')->user()->status == 0) {
            session()->flash('warning', trans('admin::app.users.activate-warning'));

            auth()->guard('user')->logout();

            return redirect()->route('admin.session.create');
        }

        $menus = menu()->getItems('admin');

        $availableNextMenu = $menus?->first();

        if (! bouncer()->hasPermission('dashboard')) {
            if (is_null($availableNextMenu)) {
                session()->flash('error', trans('admin::app.users.not-permission'));

                auth()->guard('user')->logout();

                return redirect()->route('admin.session.create');
            }

            return redirect()->to($availableNextMenu->getUrl());
        }

        $hasAccessToIntendedUrl = $this->canAccessIntendedUrl($menus, redirect()->getIntendedUrl());

        if ($hasAccessToIntendedUrl) {
            return redirect()->intended(route('admin.dashboard.index'));
        }

        return redirect()->to($availableNextMenu->getUrl());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(): RedirectResponse
    {
        if ($user = auth()->guard('user')->user()) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'logout',
                'entity_type' => 'users',
                'entity_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'payload' => null,
            ]);
        }

        session()->forget('two_factor_authenticated_at');

        auth()->guard('user')->logout();

        return redirect()->route('admin.session.create');
    }

    /**
     * Find menu item by URL.
     */
    protected function canAccessIntendedUrl(Collection $menus, ?string $url): ?MenuItem
    {
        if (is_null($url)) {
            return null;
        }

        foreach ($menus as $menu) {
            if ($menu->getUrl() === $url) {
                return $menu;
            }

            if ($menu->haveChildren()) {
                $found = $this->canAccessIntendedUrl($menu->getChildren(), $url);

                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }
}
