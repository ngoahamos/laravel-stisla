<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{

    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, HasUlids;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'telephone',
        'facility_id',
        'password'
    ];

    protected $attributes = ['status' => 1];

    protected $appends = [ 'status_description'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getStatusDescriptionAttribute(): string
    {
        return match ($this->attributes['status']) {
            1 => 'Active',
            0 => 'Blocked',
            default => 'Suspended',
        };
    }

    public function isAdmin()
    {
        return $this->attributes['role_name'] == 'admin';
    }

    public function facilities()
    {
        return $this->hasMany(UserFacility::class, 'user_id');
    }

    /*
     * This is the default facility
     */
    public function facility()
    {
        return $this->belongsTo(UserFacility::class,'facility_id');

    }
}
