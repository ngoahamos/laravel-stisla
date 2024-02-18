<?php

use App\Http\Controllers\LoanCategoryController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth'], 'prefix' => 'settings', 'as' => 'settings.'], function(){

    Route::get('loan-categories', [LoanCategoryController::class, 'index'])->name('loan-categories');

    Route::get('create-loan-category', [LoanCategoryController::class, 'create'])->name('create-loan-category');

    Route::get('edit-loan-category/{id}', [LoanCategoryController::class, 'edit'])->name('edit-loan-category');

    Route::post('store-loan-category', [LoanCategoryController::class, 'store'])->name('store-loan-category');

    Route::put('update-loan-category/{id}', [LoanCategoryController::class, 'update'])->name('update-loan-category');

});
