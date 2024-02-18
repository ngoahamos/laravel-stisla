<?php

namespace App\Models;

use App\Scopes\BranchIDScope;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = ['company_id', 'name', 'code'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new BranchIDScope());
        static::addGlobalScope(new CompanyScope);
    }

    /**
     * Link to user model
     *
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction');
    }

    public function balances()
    {
        return $this->hasManyThrough('App\Models\Balance', 'App\Models\Customer');
    }
}
