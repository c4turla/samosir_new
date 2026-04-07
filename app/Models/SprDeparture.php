<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SprDeparture extends Model
{
    use SoftDeletes;

    protected $table = 'spr_departures';

    protected $fillable = [
        'vessel_id',
        'user_id',
        'nakhoda_name',
        'muatan',
        'cp_arrival_date',
        'cp_arrival_stbl',
        'cp_departure_date',
        'cp_departure_stbl',
        'physical_arrival_date',
        'physical_arrival_stbl',
        'physical_departure_date',
        'physical_departure_stbl',
        'additional_notes',
        'planned_departure_datetime',
        'status'
    ];

    protected $casts = [
        'cp_arrival_date' => 'date',
        'cp_departure_date' => 'date',
        'physical_arrival_date' => 'date',
        'physical_departure_date' => 'date',
        'planned_departure_datetime' => 'datetime',
    ];

    /**
     * Get the vessel associated with the SPR.
     */
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    /**
     * Get the user (manager) who submitted the SPR.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
