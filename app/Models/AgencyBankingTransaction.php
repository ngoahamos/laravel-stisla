<?php

namespace App\Models;

use App\Scopes\MultitenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgencyBankingTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['agency_banking_id', 'agency_banking_service_id', 'amount', 'description', 'user_id',
        'company_id', 'branch_id', 'phone_number','account_number','date'];


    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new MultitenantScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agency()
    {
        return $this->belongsTo(AgencyBanking::class , 'agency_banking_id');
    }

    public function service()
    {
        return $this->belongsTo(AgencyBankingService::class, 'agency_banking_service_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
