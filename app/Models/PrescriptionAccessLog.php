<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionAccessLog extends Model
{
    protected $table = 'prescription_access_log';

    protected $fillable = [
        'admin_user_id',
        'patient_id',
        'action',
        'document',
        'ip_address',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }
}
