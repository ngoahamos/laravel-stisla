<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserFacility extends Model
{
    use HasFactory, SoftDeletes, HasUlids;

    protected $fillable = ['facility_loc_id', 'user_id', 'facility_name', 'facility_lat', 'facility_lng'];


}
