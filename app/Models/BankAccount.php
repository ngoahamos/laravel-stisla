<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'number', 'branch', 'company_id', 'balance'];

    protected $appends = ['description'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }

    public function getDescriptionAttribute(): string
    {
        return "$this->name | $this->branch | $this->number";
    }

}
