<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Departure extends Model
{
    use SoftDeletes;

    protected $table = 'departures';

    protected $fillable = [
        'nomor', 'vessel_id', 'nakhoda_name', 'destination', 'crew_count', 'departure_date', 'departure_time',
        'departure_datetime', 'arrival_datetime', 'etmal_days', 'etmal_hours', 'landing_site_id', 'syahbandar',
        'ice_supply', 'water_supply', 'diesel_supply',
        'oil_supply', 'gasoline_supply', 'other_supplies', 'notes', 'status', 'floating_status', 'unloading_status', 'admin_completion',
        'approval_status', 'approved_by', 'approved_at', 'input_by', 'signature'
    ];

    protected $casts = [
        'departure_date' => 'date',
        'departure_time' => 'datetime:H:i:s',
        'departure_datetime' => 'datetime',
        'arrival_datetime' => 'datetime',
        'ice_supply' => 'integer',
        'water_supply' => 'integer',
        'diesel_supply' => 'integer',
        'oil_supply' => 'integer',
        'gasoline_supply' => 'integer',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function landingSite(): BelongsTo
    {
        return $this->belongsTo(LandingSite::class, 'landing_site_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function inputBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('approval_status', '0');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', '1');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('departure_date', today());
    }
}