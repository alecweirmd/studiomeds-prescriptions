<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountRedemption extends Model
{
    protected $table = 'discount_redemptions';

    protected $fillable = [
        'discount_code_id',
        'patient_id',
        'session_id',
        'attempt_outcome',
        'discount_amount_applied',
        'redeemed_at',
    ];

    protected $casts = [
        'redeemed_at'             => 'datetime',
        'discount_amount_applied' => 'decimal:2',
    ];

    public function discountCode()
    {
        return $this->belongsTo(DiscountCode::class, 'discount_code_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }
}
