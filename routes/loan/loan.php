<?php

use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth'], 'prefix' => 'loans', 'as' => 'loans.'], function(){

    Route::get('', [LoanController::class, 'index'])->name('index');
    Route::get('request', [LoanController::class, 'loanRequest'])->name('request');
    Route::get('active', [LoanController::class, 'activeLoans'])->name('active');
    Route::get('transactions', [LoanController::class, 'transactions'])->name('transactions');

});
