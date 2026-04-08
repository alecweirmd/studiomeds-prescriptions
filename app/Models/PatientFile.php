<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Patients;

class PatientFile extends Model
{
    //
	protected $table = 'patient_files';
    
    protected $fillable = [
    'artist_id',
    'patient_id',
    'filename',
    'file_path'
];
	
    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }
	
	
}
