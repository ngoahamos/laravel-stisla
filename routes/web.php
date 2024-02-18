<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', '/dashboards/home');

include 'loan/authenticate.php';

include 'loan/dashboard.php';
include 'loan/loan.php';
include "loan/settings.php";
include  'loan/report.php';

Route::get('/fix-balances', function (){

    $loans = \App\Models\Loan::with('balance')->get();
    echo 'Total ' . $loans->count();
    $count = 0;
    $loanBalanceService = app()->make(\App\Services\LoanBalanceService::class);
    foreach ($loans as $loan) {
        if ($loan->balance == null) {
            $loanBalanceService->grantLoan($loan->id, 0, $loan->loan_account_id, $loan->company_id, $loan->branch_id);
            $count ++;
        }
    }

    echo 'Fixed ' . $count;


});
