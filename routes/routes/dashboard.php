<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth'], 'prefix' => 'dashboards', 'as' => 'dashboards.'], function(){

    Route::get('/home', [DashboardController::class, 'home'])->name('home');

    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');

    Route::post('/change-my-password', [DashboardController::class, 'changeMyPassword'])->name('change-my-password');
    Route::post('/change-my-dp', [DashboardController::class, 'changeMyDp'])->name('change-my-dp');

});
