<?php

namespace App\Models;

use App\Scopes\CompanyIDScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'address', 'telephone', 'raw_logo', 'color', 'account_prefix', 'email',
        'website', 'showLoan', 'sms_sender_id'];

    protected $appends = ['logo', 'avatar1', 'avatar2', 'number_of_users', 'number_of_branches'];

    protected $casts = ['showLoan' => 'boolean'];
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new CompanyIDScope);
    }

    /**
     * Generates Logo url.
     *
     * @return string
     */
    public function getLogoAttribute()
    {
        return $this->attributes['raw_logo'] == null ?
            image_placeholder('150x150', null, str_first_character($this->name))
            :
            Storage::url($this->attributes['raw_logo']);
    }

    /**
     * Generates Avatar 1 url.
     *
     * @return string
     */
    public function getAvatar1Attribute()
    {
        return image_placeholder('552x222', 'ffffff', str_first_character($this->name), '49a9ee');
    }

    /**
     * Generates Avatar 2 url.
     *
     * @return string
     */
    public function getAvatar2Attribute()
    {
        return image_placeholder('222x222', 'ffffff', str_first_character($this->name), '49a9ee');
    }

    /**
     * Get Number of Users
     *
     * @return string
     */
    public function getNumberOfUsersAttribute()
    {
        return $this->users()->count();
    }

    /**
     * Get Number of Branches
     *
     * @return string
     */
    public function getNumberOfBranchesAttribute()
    {
        return $this->branches()->count();
    }

    /**
     * Link to user model
     *
     *
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function setAccountPrefixAttribute($prefix)
    {
        if (empty($prefix))
        {
            $prefix = str_first_character($this->name) . '_';
        }

        $this->attributes['account_prefix'] = $prefix;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function balances()
    {
        return $this->hasManyThrough(Balance::class, Customer::class);
    }
}
