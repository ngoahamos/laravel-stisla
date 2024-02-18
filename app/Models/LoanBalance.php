<?php

namespace App\Models;

use App\Scopes\MultitenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanBalance extends Model
{
    use SoftDeletes;

    protected $fillable = ['loan_account_id', 'amount', 'company_id','branch_id','loan_id'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new MultitenantScope);
    }

    public function loan()
    {
        return $this->belongsTo(Loan);
    }
}
