<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    protected $table = 'discount_codes';

    protected $fillable = [
        'code_string',
        'partner_name',
        'discount_type',
        'discount_value',
        'usage_cap',
        'usage_count',
        'expiration_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'discount_value'  => 'decimal:2',
    ];

    public function redemptions()
    {
        return $this->hasMany(DiscountRedemption::class, 'discount_code_id');
    }
}
