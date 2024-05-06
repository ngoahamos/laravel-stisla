<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class VerificationToken extends Model
{
    use HasFactory, SoftDeletes, HasUlids;


    protected $fillable = ['token', 'user_id', 'otp', 'valid_before'];

    protected $casts =[
      'valid_before' => 'datetime'
    ];
}
