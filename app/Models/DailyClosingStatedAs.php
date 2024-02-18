<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyClosingStatedAs extends Model
{
    use HasFactory, SoftDeletes;

    public static $snakeAttributes = false;

    protected $fillable = ['daily_closing_id', 'bank_account_id', 'amount'];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }
}
