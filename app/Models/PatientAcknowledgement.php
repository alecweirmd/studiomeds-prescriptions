<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientAcknowledgement extends Model
{
    protected $fillable = [
        'patient_id',
        'session_id',
        'ip_address',
        'triggered_questions',
        'acknowledged_at',
        'pdf_path',
    ];

    protected $casts = [
        'triggered_questions' => 'array',
        'acknowledged_at'     => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }
}
