<?php

use Modules\Auth\Http\Controllers\UserController;

Route::prefix('v1')->group(function () {
    Route::post('login', [UserController::class, 'login']);
    Route::post('users/forgot-password', [UserController::class, 'forgotPassword']);
    Route::post('users/reset-password', [UserController::class, 'resetPassword']);
    Route::post('logout', [UserController::class, 'logout']);

    Route::get('users/me', [UserController::class, 'me']);
    Route::apiResource('users', UserController::class);
});
