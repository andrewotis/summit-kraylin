<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\User\TwoFactorController;

Route::withoutMiddleware(['two-factor', 'idle-timeout'])->group(function () {
    Route::controller(TwoFactorController::class)->prefix('two-factor')->group(function () {
        Route::get('setup', 'setup')->name('admin.two-factor.setup');

        Route::post('enable', 'enable')->name('admin.two-factor.enable');

        Route::get('challenge', 'challenge')->name('admin.two-factor.challenge');

        Route::post('verify', 'verify')->name('admin.two-factor.verify');

        Route::get('recovery-codes', 'recoveryCodes')->name('admin.two-factor.recovery-codes');

        Route::post('regenerate-recovery-codes', 'regenerateRecoveryCodes')->name('admin.two-factor.regenerate-recovery-codes');

        Route::post('disable', 'disable')->name('admin.two-factor.disable');

        Route::post('logout', 'logout')->name('admin.two-factor.logout');
    });
});
