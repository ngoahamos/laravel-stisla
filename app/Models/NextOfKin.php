<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NextOfKin extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'contact', 'relationship', 'mandate', 'customer_id'];
}
