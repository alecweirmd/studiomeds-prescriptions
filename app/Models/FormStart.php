<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormStart extends Model
{
    protected $fillable = [
        'email',
        'ip_address',
        'session_id',
        'started_at',
        'completed',
        'patient_id',
        'abandoned_at',
        'contacted_at',
        'dismissed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'abandoned_at' => 'datetime',
        'contacted_at' => 'datetime',
        'dismissed_at' => 'datetime',
        'completed'    => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }
}
