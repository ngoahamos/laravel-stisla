<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyClosingCashAtHand extends Model
{
    use HasFactory, SoftDeletes;

    public static $snakeAttributes = false;

    protected $fillable = ['daily_closing_id', 'denomination_id', 'amount', 'num'];

    public function denomination()
    {
        return $this->belongsTo(Denomination::class);
    }
}
