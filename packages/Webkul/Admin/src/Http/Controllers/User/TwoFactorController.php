<?php

namespace Webkul\Admin\Http\Controllers\User;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;
use Webkul\Admin\Http\Controllers\Controller;

class TwoFactorController extends Controller
{
    public function __construct(protected Google2FA $google2fa) {}

    public function setup(): View
    {
        $user = auth()->guard('user')->user();

        $secret = $this->google2fa->generateSecretKey();

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('admin::user.two-factor.setup', compact('secret', 'qrCodeUrl'));
    }

    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'secret' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->guard('user')->user();

        if (! $this->google2fa->verifyKey($request->secret, $request->code)) {
            return redirect()->back()->withErrors(['code' => 'The verification code is invalid.']);
        }

        $user->forceFill([
            'google2fa_secret' => $request->secret,
            'two_factor_recovery_codes' => json_encode($this->generateRecoveryCodes()),
            'two_factor_confirmed_at' => now(),
        ])->save();

        session(['two_factor_authenticated_at' => now()->timestamp]);

        return redirect()->route('admin.two-factor.recovery-codes')->with('success', 'Two-factor authentication has been enabled.');
    }

    public function challenge(): View
    {
        return view('admin::user.two-factor.challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth()->guard('user')->user();

        if ($this->google2fa->verifyKey($user->google2fa_secret, $request->code)) {
            session(['two_factor_authenticated_at' => now()->timestamp]);

            return redirect()->intended(route('admin.dashboard.index'));
        }

        if ($this->isValidRecoveryCode($user, $request->code)) {
            session(['two_factor_authenticated_at' => now()->timestamp]);

            return redirect()->intended(route('admin.dashboard.index'));
        }

        return redirect()->back()->withErrors(['code' => 'The provided code is invalid.']);
    }

    public function recoveryCodes(): View
    {
        $user = auth()->guard('user')->user();

        $codes = json_decode($user->two_factor_recovery_codes ?? '[]', true);

        return view('admin::user.two-factor.recovery-codes', compact('codes'));
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->guard('user')->user();

        if (! $this->google2fa->verifyKey($user->google2fa_secret, $request->code)) {
            return redirect()->back()->withErrors(['code' => 'The verification code is invalid.']);
        }

        $user->forceFill([
            'two_factor_recovery_codes' => json_encode($this->generateRecoveryCodes()),
        ])->save();

        return redirect()->route('admin.two-factor.recovery-codes')->with('success', 'Recovery codes have been regenerated.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth()->guard('user')->user();

        if (! $this->google2fa->verifyKey($user->google2fa_secret, $request->code)) {
            if (! $this->isValidRecoveryCode($user, $request->code)) {
                return redirect()->back()->withErrors(['code' => 'The verification code is invalid.']);
            }
        }

        $user->forceFill([
            'google2fa_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        session()->forget('two_factor_authenticated_at');

        return redirect()->route('admin.account.edit')->with('success', 'Two-factor authentication has been disabled.');
    }

    public function logout(): RedirectResponse
    {
        session()->forget('two_factor_authenticated_at');

        auth()->guard('user')->logout();

        return redirect()->route('admin.session.create');
    }

    protected function generateRecoveryCodes(): array
    {
        $codes = [];

        for ($i = 0; $i < 10; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        }

        return $codes;
    }

    protected function isValidRecoveryCode($user, string $code): bool
    {
        if (! $user->two_factor_recovery_codes) {
            return false;
        }

        $codes = json_decode($user->two_factor_recovery_codes, true);

        $index = array_search($code, $codes);

        if ($index === false) {
            return false;
        }

        unset($codes[$index]);

        $user->forceFill([
            'two_factor_recovery_codes' => json_encode(array_values($codes)),
        ])->save();

        return true;
    }
}
