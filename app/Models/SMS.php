<?php

namespace App\Models;

use App\Scopes\MultitenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SMS extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['message', 'receiver_id', 'contact','cost',
        'user_id', 'company_id', 'branch_id', 'delivery_status',
        'campaign_id'
    ];

    protected $appends = ['pretty_contact'];

    public function getPrettyContactAttribute()
    {
        return mb_strlen($this->contact) > 12 ? substr($this->contact, 0,10) . ', 2333***' : $this->contact;
    }

    protected static function booted()
    {

        static::addGlobalScope(new MultiTenantScope());

    }

    public function user()
    {
       return $this->belongsTo(User::class);
    }
}
