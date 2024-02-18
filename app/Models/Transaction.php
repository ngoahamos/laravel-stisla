<?php

namespace App\Models;

use App\Contracts\TransactionType;
use App\Scopes\MultitenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = ['customer_id', 'date', 'type', 'user_id', 'comment', 'amount', 'company_id', 'branch_id',
        'balance', 'donor_phone'];

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
        switch ($this->attributes['type'])
        {
            case TransactionType::$DEPOSIT:
                return 'Deposit';
            case TransactionType::$WITHDRAWAL:
                return 'Withdrawal';
            default:
                return '';
        }
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
