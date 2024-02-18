<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function pendingApproval()
    {

       $loans = Loan::with(['loanAccount', 'loanAccount.guarantor', 'loanAccount.IDType', 'agent', 'approvedBy',
            'branch','category', 'balance'])
           ->withSum('balance','amount')
           ->where([['isApproved', 0], ['isRejected', 0], ['isPayed', 0]]);

       $totals_clone = $loans->clone();

       $loans = $loans->paginate(5);

       $principal = $totals_clone->sum('principal');
       $amount = $totals_clone->sum('amount');
       $interest = $totals_clone->sum('interestAmount');

       $balance =  $totals_clone->get()->sum('balance.amount');

       $repayment = $amount - $balance;


       return view('pages.reports.pending', ['loans' => $loans, 'principal' => $principal, 'amount' => $amount,
           'interest' => $interest, 'repayment' => $repayment, 'balance' => $balance]);

    }
}
