<?php

namespace App\Models;

use App\Contracts\LoanTransactionType;
use App\Scopes\MultitenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanTransaction extends Model
{
    use SoftDeletes;
    public static $snakeAttributes = false;
    protected $fillable = ['loan_account_id','loan_id','loan_category_id', 'date', 'type', 'user_id', 'comment',
        'amount', 'company_id', 'branch_id', 'balance'];

    protected $appends = ['description'];
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new MultitenantScope);
    }

    public function getDescriptionAttribute()
    {
        return match ($this->attributes['type']) {
            LoanTransactionType::$LOAN => 'Loan',
            LoanTransactionType::$REPAYMENT => 'Repayment',
            default => '',
        };
    }
    public function loanAccount()
    {
        return $this->belongsTo(LoanAccount::class, 'loan_account_id');
    }

    public function category()
    {
        return $this->belongsTo(LoanCategory::class, 'loan_category_id');
    }
    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function loan() {
        return $this->belongsTo(Loan::class);
    }
}
