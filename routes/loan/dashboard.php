<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth'], 'prefix' => 'dashboards', 'as' => 'dashboards.'], function(){

    Route::get('/home', [DashboardController::class, 'home'])->name('home');
    Route::get('/branch-totals', [DashboardController::class, 'branchesOverallBalances'])->name('branch-totals');
    Route::get('/overall-total', [DashboardController::class, 'overallBalances'])->name('overall-total');

    Route::get('/branch-today-totals', [DashboardController::class, 'branchesOverAllBalancesAt'])->name('branch-today-totals');
    Route::get('/branch-transactions', [DashboardController::class, 'branchTransactions'])->name('branch-transactions');
    Route::get('/compare-analytic', [DashboardController::class, 'compareAnalytic'])->name('compare-analytic');
    Route::get('/export-top-balances', [DashboardController::class, 'exportTopBalances'])->name('export-top-balances');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');

    Route::post('/change-my-password', [DashboardController::class, 'changeMyPassword'])->name('change-my-password');
    Route::post('/change-my-dp', [DashboardController::class, 'changeMyDp'])->name('change-my-dp');

});
