<?php

use Illuminate\Support\Facades\Route;
use Webkul\Marketing\Http\Controllers\UnsubscribeController;

Route::get('/unsubscribe/{id}', UnsubscribeController::class)->name('marketing.unsubscribe');
