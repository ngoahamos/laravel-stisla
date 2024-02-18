<?php

namespace App\Models;

use App\Scopes\MultitenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyClosing extends Model
{
    use HasFactory, SoftDeletes;

    public static $snakeAttributes = false;

    protected $fillable = ['date', 'total_deposit', 'total_withdrawal', 'previous', 'user_id', 'company_id', 'branch_id', 'gross'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new MultitenantScope);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function agents()
    {
        return $this->hasMany(DailyClosingAgent::class, 'daily_closing_id');
    }

    public function cashAtHand()
    {
        return $this->hasMany(DailyClosingCashAtHand::class, 'daily_closing_id');
    }

    public function statedAs()
    {
        return $this->hasMany(DailyClosingStatedAs::class, 'daily_closing_id');
    }
}
