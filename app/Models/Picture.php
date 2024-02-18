<?php

namespace App\Models;

use App\Scopes\MultitenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Picture extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['type', 'type_id', 'raw_path', 'branch_id', 'company_id'];

    protected $appends = ['path'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new MultitenantScope);
    }

    public function getPathAttribute()
    {
        return Storage::url($this->attributes['raw_path']);
    }
}
