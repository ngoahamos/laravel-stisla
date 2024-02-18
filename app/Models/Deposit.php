<?php

namespace App\Models;

use App\Scopes\MultitenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deposit extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = ['description', 'bank_account_id', 'amount', 'date','company_id', 'branch_id', 'user_id', 'type'];

    protected $casts = ['amount' => 'float'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new MultitenantScope);
    }

    public function bank() {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function company() {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function agent() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pictures() {
        return $this->hasMany(Picture::class, 'type_id')
                    ->where('type', 'bank-transactions');

    }
}
