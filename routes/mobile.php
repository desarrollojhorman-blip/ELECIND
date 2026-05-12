<?php

use Illuminate\Support\Facades\Route;

Route::prefix('m')->name('mobile.')->group(function (): void {
    Route::view('/', 'mobile.dashboard')->name('dashboard');
});
