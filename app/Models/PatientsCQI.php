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
    ];

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }
}
