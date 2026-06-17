<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Generalized polymorphic alert surfaced in the unified clinician Alerts tab.
 * Thin index over a typed payload (alertable). See create_alerts_table migration.
 */
class Alert extends Model
{
    protected $table = 'alerts';

    protected $fillable = [
        'type',
        'alertable_type',
        'alertable_id',
        'metadata',
        'resolved_at',
        'resolved_by_admin_user_id',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'resolved_at' => 'datetime',
    ];

    public function alertable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Unresolved alerts only — what the Alerts tab displays.
     */
    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereNull('resolved_at');
    }
}
