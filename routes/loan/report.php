<?php

use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth'], 'prefix' => 'reports', 'as' => 'reports.'], function(){

    Route::get('pending-approval', [ReportController::class, 'pendingApproval'])->name('pending-approval');
    Route::get('defaulted', [ReportController::class, 'defaultedLoans'])->name('defaulted');
    Route::get('bad-debts', [ReportController::class, 'badDebts'])->name('bad-debts');
//    Route::get('pending-approval', [ReportController::class, 'index'])->name('pending-approval');



});
