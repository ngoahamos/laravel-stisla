<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanAccountGuarantor extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'contact', 'occupation', 'relationship', 'mandate', 'loan_account_id'];
}
