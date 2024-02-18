<?php

namespace App\Models;

use App\Scopes\MultitenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Customer extends Model
{
    use SoftDeletes;

    public static $snakeAttributes = false;

    protected $fillable = ['title', 'surname', 'middle_name', 'other_names', 'date_of_birth', 'sex', 'region_id',
        'city', 'telephone', 'date_of_registration', 'id_type_id', 'id_number', 'product_id',
        'user_id', 'nature_of_business', 'branch_id', 'company_id', 'account_number', 'commission',
        'standing_order', 'raw_picture', 'raw_signature', 'status', 'sms', 'passbook_number', 'tag', 'tagged_at','special_message',
        'special_top_message'];


    protected $appends = ['picture', 'temp_picture', 'temp_signature', 'signature', 'full_name', 'formal_name', 'legal_name',
        'status_description', 'tag_description'];

    protected $casts = [
        'tagged_at' => 'datetime'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new MultitenantScope);
    }

    public function getStatusDescriptionAttribute()
    {
        return match ($this->status) {
            1 => 'Active',
            2 => 'Dormant',
            3 => 'Closed',
            default => 'Unknown',
        };
    }

    public function getTagDescriptionAttribute()
    {

        return match ($this->tag) {
            "1" => 'Active',
            "2" => 'Dormant',
            "3" => 'Closed',
            default => 'Unknown',
        };
    }


    public function getPictureAttribute()
    {
        return $this->raw_picture == null ?
            image_placeholder(null, null, str_first_character($this->name()))  :
            Storage::url($this->raw_picture);
    }

    public function getTempPictureAttribute()
    {
        return $this->raw_picture == null ?
            image_placeholder(null, null, str_first_character($this->name()))  :
            Storage::url($this->raw_picture);
        // get_api_temp_base_ur() . '/storage/'. $this->raw_picture;
    }

    public function getLegalNameAttribute()
    {
        return strtoupper($this->name());
    }

    public function getFullNameAttribute()
    {
        return strtoupper($this->selectName());
    }

    public function getFormalNameAttribute()
    {
        return strtoupper($this->formalName());
    }

    public function getSignatureAttribute()
    {
        return $this->raw_signature == null ?
            image_placeholder(null, null, 'Signature')  :
            Storage::url($this->raw_signature);
    }

    /*
     * @Deprecated
     */
    public function getTempSignatureAttribute(): string
    {
        return $this->raw_signature == null ?
            image_placeholder(null, null, 'Signature')  :
            Storage::url($this->raw_signature);
           // get_api_temp_base_ur() . '/storage/'. $this->raw_signature;
    }

    public function setSmsAttribute($sms)
    {
        $this->attributes['sms'] = boolean_to_int($sms);
    }

    private function name() {
        $first = $this->other_names ?? '';
        $surname = $this->surname ?? '';

        return $first . ' '. $surname;
    }

    private function formalName() {
        $first = $this->other_names ?? '';
        $surname = $this->surname ?? '';

        return $this->title . ' ' . $surname . ', '. $first;
    }

    private function selectName() {
        return $this->name() . ' ' . $this->passbook_number;
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }

    public function nextOfKin()
    {
        return $this->hasOne(NextOfKin::class, 'customer_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function IDType()
    {
        return $this->belongsTo(IDType::class, 'id_type_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function balance()
    {
        return $this->hasOne(Balance::class);
    }
}
