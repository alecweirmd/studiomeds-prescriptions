<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UtmVisit extends Model
{
    protected $table = 'utm_visits';

    protected $fillable = [
        'session_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'first_touch_at',
        'last_touch_at',
        'completed',
        'patient_id',
    ];

    protected $casts = [
        'first_touch_at' => 'datetime',
        'last_touch_at'  => 'datetime',
        'completed'      => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }
}
