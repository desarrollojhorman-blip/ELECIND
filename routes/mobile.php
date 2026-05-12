<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure.mobile.access'])->prefix('m')->name('mobile.')->group(function (): void {
    Route::view('/', 'mobile.dashboard')->name('dashboard');
});
