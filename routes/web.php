<?php
use Illuminate\Support\Facades\Route;

Route::prefix('/vendor/rohsyl/laravel-otc')
    //->middleware(['throttle:' . config('otc.rate-limit.name', 'laravel-otc')])
    ->group(function() {

    Route::post('/auth/request-code', \rohsyl\LaravelOtc\Http\Controllers\RequestCodeController::class)->name('laravel-otc.request-code');
    Route::post('/auth/code', \rohsyl\LaravelOtc\Http\Controllers\AuthController::class)->name('laravel-otc.auth-code');

});
