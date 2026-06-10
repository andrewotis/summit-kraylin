<?php

namespace Webkul\Admin\Http\Controllers\User;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Models\AuditLog;
use Webkul\User\Models\User;

class EmergencyAccessController extends Controller
{
    public function grantForm(): View
    {
        return view('admin::user.emergency.grant');
    }

    public function grant(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $granter = auth()->guard('user')->user();

        $target = User::where('email', $request->email)->first();

        $token = Str::random(64);

        $target->forceFill([
            'emergency_token' => Hash::make($token),
            'emergency_token_expires_at' => now()->addHour(),
            'emergency_granted_by' => $granter->id,
        ])->save();

        AuditLog::create([
            'user_id' => $granter->id,
            'action' => 'emergency_access_granted',
            'entity_type' => 'users',
            'entity_id' => $target->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => ['granted_to' => $target->email],
        ]);

        session()->flash('emergency_token', $token);

        return redirect()->route('admin.emergency.grant.form');
    }

    public function accessForm(): View
    {
        return view('admin::user.emergency.access');
    }

    public function access(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (
            ! $user->emergency_token
            || ! $user->emergency_token_expires_at
            || now()->gt($user->emergency_token_expires_at)
        ) {
            session()->flash('error', trans('admin::app.users.emergency.token-expired'));

            return redirect()->back();
        }

        if (! Hash::check($request->token, $user->emergency_token)) {
            session()->flash('error', trans('admin::app.users.emergency.invalid-token'));

            return redirect()->back();
        }

        $user->forceFill([
            'emergency_token' => null,
            'emergency_token_expires_at' => null,
            'emergency_granted_by' => null,
        ])->save();

        auth()->guard('user')->login($user);

        session([
            'logged_in_at' => now()->timestamp,
            'two_factor_authenticated_at' => now()->timestamp,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'emergency_access_used',
            'entity_type' => 'users',
            'entity_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => null,
        ]);

        if ($user->status == 0) {
            session()->flash('warning', trans('admin::app.users.activate-warning'));

            auth()->guard('user')->logout();

            return redirect()->route('admin.session.create');
        }

        return redirect()->route('admin.dashboard.index');
    }
}
