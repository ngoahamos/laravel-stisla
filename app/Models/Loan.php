<?php

namespace App\Models;

use App\Scopes\MultitenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use  SoftDeletes;

    public static $snakeAttributes = false;

    protected $fillable = ['loan_account_id', 'principal', 'interest', 'interestAmount','amount', 'user_id','due_date','approved_by_id',
        'duration_month','duration_days', 'amount_per_month','date','approved_date', 'set_as_bad_user_id', 'bad_date',
        'amount_per_day', 'loan_category_id', 'company_id', 'branch_id'];

    protected $casts = [
        'isApproved' => 'boolean',
        'isDefaulted'  => 'boolean',
        'isPayed' => 'boolean',
        'isRejected' => 'boolean',
        'isBad' => 'boolean',
        'due_date'=> 'date'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new MultitenantScope);
    }

    public function setIsApprovedAttribute($val)
    {
        $this->attributes['isApproved'] = boolean_to_int($val);
    }

    public function setIsPayedAttribute($val)
    {
        $this->attributes['isPayed'] = boolean_to_int($val);
    }

    public function setIsDefaultedAttribute($val)
    {
        $this->attributes['isDefaulted'] = boolean_to_int($val);
    }

    public function setIsBadAttribute($val)
    {
        $this->attributes['isBad'] = boolean_to_int($val);
    }
    public function loanAccount()
    {
        return $this->belongsTo(LoanAccount::class, 'loan_account_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function category()
    {
        return $this->belongsTo(LoanCategory::class, 'loan_category_id');
    }

    public function transactions()
    {
        return $this->hasMany(LoanTransaction::class,'loan_id')->where('type',1);
    }

    public function balance()
    {
        return $this->hasOne(LoanBalance::class, 'loan_id');
    }
}
