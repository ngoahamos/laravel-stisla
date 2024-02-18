<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyClosingAgent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['daily_closing_id', 'user_id', 'amount'];

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
