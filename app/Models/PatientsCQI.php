<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientsCQI extends Model
{
    protected $table = 'patients_cqi';

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }
}
