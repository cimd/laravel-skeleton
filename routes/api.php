<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\UserController;

Route::middleware('auth:api')->get('/user', [UserController::class, 'user']);
