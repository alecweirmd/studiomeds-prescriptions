<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientsCQI extends Model
{
    protected $table = 'patients_cqi';

    protected $fillable = [
        'artist_id',
        'status',
        'patient_id',
        'lidocaine',
        'bactine',
        'broken_skin',
        'eczema',
        'heart_rhythm',
        'liver_disease',
        'seizures',
        'pregnant',
        'antiarrhythmic',
        'seizure_meds',
        'fainted',
        'methemoglobinemia',
        'lip_cold_sore_active',
        'eye_infection_active',
        'recent_eye_surgery',
        'contacts_cannot_remove',
        'severe_dry_eye',
        'follow_up_sent_at',
        'reengagement_sent_at',
        'review_sent_at',
    ];

    protected $casts = [
        'follow_up_sent_at'    => 'datetime',
        'reengagement_sent_at' => 'datetime',
        'review_sent_at'       => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }
}
