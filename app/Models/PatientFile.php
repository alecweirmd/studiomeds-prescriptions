<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
	
	public function client()
    {
        return $this->belongsTo(Client::class, 'patient_id');
    }
	
	
}
