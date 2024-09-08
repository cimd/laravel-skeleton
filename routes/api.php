<?php

use Illuminate\Support\Facades\Route;
use Modules\Application\Http\Controllers\UserController;

Route::middleware('auth:api')->get('/user', [UserController::class, 'user']);
