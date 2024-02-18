<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SMSBalance extends Model
{
    use HasFactory,  SoftDeletes;

    protected $fillable = ['balance', 'company_id', 'sender_id', 'user_id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
