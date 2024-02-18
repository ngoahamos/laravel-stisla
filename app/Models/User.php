<?php

namespace App\Models;

use App\Scopes\MultitenantScope;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes, HasApiTokens;

    public static $snakeAttributes = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'password', 'telephone', 'id_type_id', 'id_number', 'raw_picture'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    protected $appends = ['avatar'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new MultitenantScope);
    }

    /**
     * Get User current Status.
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        switch ($this->attributes['status']) {
            case 1:
                return 'Active';
            case 2:
                return 'Suspended';
            default:
                return 'Blocked';
        }
    }

    /**
     * Generates user's profile picture url.
     *
     * @return string
     */
    public function getAvatarAttribute()
    {
        return $this->attributes['raw_picture'] == null ?
            image_placeholder('150x150', null, str_first_character($this->name))
            :
            Storage::url($this->attributes['raw_picture']);
    }

    /**
     * Gets user using username column.
     *
     * @param $username
     * @return User | null
     */
    public function findForPassport($username)
    {
        return $this->where('username',$username)->first();
    }

    /**
     * Checks if user is super user
     *
     * @return bool
     */
    public function isSuper()
    {
        return $this->role_name ==  'super';
    }

    /**
     * Checks if user is director
     *
     * Director manages a company
     *
     * @return bool
     */
    public function isDirector()
    {
        return $this->role_name == 'director';
    }

    /**Check if user is manager.
     *
     * Managers control a branch.
     *
     * @return bool
     */
    public function isManager()
    {
        return $this->role_name == 'manager';
    }

    /**
     * Checks if user is agent.
     *
     *
     * @return bool
     */
    public function isAgent()
    {
        return $this->role_name == 'agent';
    }

    /**
     * Checks if user has either manager, director or agent role
     *
     * @return bool
     */
    public function isCompanyLevel()
    {
        return $this->hasAnyRole(['manager', 'director', 'agent']);
    }

    /**
     * Checks if user has either manager, super or director role
     *
     * @return bool
     */
    public function isTopLevel()
    {
        return $this->hasAnyRole(['manager', 'super', 'director']);
    }

    /**
     * Checks if user has any of the specified roles
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles)
    {
        return in_array($this->role_name, $roles);
    }

    /**
     * Checks if user has role
     *
     * @param $role
     * @return bool
     */
    public function hasRole($role)
    {
        return is_string($role) ? $this->role_name == $role : $this->hasAnyRole($role);
    }

    /**
     * Link to Company Model
     *
     *
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Link to Branch Model
     *
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function guarantor()
    {
        return $this->hasOne(Guarantor::class);
    }

    public function idType()
    {
        return $this->belongsTo(IDType::class, 'id_type_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }
}
