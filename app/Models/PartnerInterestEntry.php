<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerInterestEntry extends Model
{
    protected $table = 'partner_interest';

    protected $fillable = [
        'name',
        'email',
        'shop_name',
        'shop_location',
        'procedure_focus',
        'source_page',
        'social_handle',
        'how_did_you_hear',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public $timestamps = false;
}
