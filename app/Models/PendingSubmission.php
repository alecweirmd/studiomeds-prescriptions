<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Preserved intake data for a submission whose Authorize.net charge succeeded
 * but whose post-charge DB transaction failed (Audit Finding #5). See the
 * create_pending_submissions_table migration for the lifecycle contract.
 */
class PendingSubmission extends Model
{
    protected $table = 'pending_submissions';

    protected $fillable = [
        'patient_id',
        'transaction_id',
        'charged_amount',
        'charged_at',
        'first_name',
        'last_name',
        'email',
        'date_of_birth',
        'street_address',
        'city',
        'state',
        'zip',
        'procedure_type',
        'artist_id',
        'artist_name',
        'name_of_shop',
        'cqi_answers',
        'drivers_license_path',
        'selfie_path',
        'verification_method',
        'didit_session_id',
        'recovery_status',
        'resolved_at',
    ];

    protected $casts = [
        'cqi_answers' => 'array',
        'charged_at'  => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function alerts(): MorphMany
    {
        return $this->morphMany(Alert::class, 'alertable');
    }
}
