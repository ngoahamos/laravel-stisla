<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanAccountAddress extends Model
{
    use SoftDeletes;

    protected $fillable = ['loan_account_id', 'residential', 'landmark', 'ghana_post_gps'];
}
