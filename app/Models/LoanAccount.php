<?php

namespace App\Models;

use App\Scopes\MultitenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class LoanAccount extends Model
{
    use SoftDeletes;

    public static $snakeAttributes = false;

    protected $fillable = ['title', 'surname', 'middle_name', 'other_names', 'date_of_birth', 'sex', 'region_id',
        'city', 'telephone', 'date_of_registration', 'id_type_id', 'id_number', 'loan_category_id',
        'user_id', 'nature_of_business', 'branch_id', 'company_id', 'account_number', 'commission',
        'standing_order', 'raw_picture', 'raw_signature', 'status', 'sms', 'passbook_number',
        'collateral', 'collateral_description'];


    protected $appends = ['picture', 'signature'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new MultitenantScope);
    }


    public function getPictureAttribute()
    {
        return $this->raw_picture == null ?
            image_placeholder(null, null, str_first_character($this->name()))  :
            Storage::url($this->raw_picture);
    }

    public function getSignatureAttribute()
    {
        return $this->raw_signature == null ?
            image_placeholder(null, null, 'Signature')  :
            Storage::url($this->raw_signature);
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

    public function address()
    {
        return $this->hasOne(LoanAccountAddress::class, 'loan_account_id');
    }

    public function guarantor()
    {
        return $this->hasOne(LoanAccountGuarantor::class, 'loan_account_id');
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

    public function category()
    {
        return $this->belongsTo(LoanCategory::class, 'loan_category_id');
    }

    public function transactions()
    {
        return $this->hasMany(LoanTransaction::class,'loan_account_id');
    }
}
