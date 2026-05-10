<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patients extends Model {

    //
    protected $table = 'patients';
    
    protected $fillable = [
        'first_name',
        'last_name',
        'artist_name',
        'name_of_shop',
        'street_address',
        'city',
        'state',
        'zip',
        'email',
        'password',
        'drivers_license',
        'patient_photo',
        'user_type',
        'procedure_type',
        'created_at',
        'updated_at',
    ];

    //tie patients to the atists
    public function user() {
        return $this->belongsTo(User::class, 'artist_id');
    }

    //Link the patients CQI applicaiton 
    public function patientsCQI() {
        return $this->hasOne(PatientsCQI::class, 'patient_id');
    }

    public function artist(){
    return $this->belongsTo(User::class, 'artist_id', 'id')
        ->withDefault();
    }
	public function patientFile()
    {
        return $this->hasOne(PatientFile::class, 'patient_id');
    }
}
