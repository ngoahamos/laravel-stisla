<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', [\App\Http\Controllers\UserController::class, 'login'])->name('login');

Route::get('/logout', [\App\Http\Controllers\UserController::class, 'logout'])
    ->name('logout');

Route::post('/login', [\App\Http\Controllers\UserController::class, 'attempt'])->name('attempt');


