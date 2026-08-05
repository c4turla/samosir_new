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
        'approval_status', 'approved_by', 'approved_at', 'input_by', 'signature', 'is_processed'
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
        'is_processed' => 'boolean',
        'approval_status' => 'boolean',
    ];

    protected $appends = ['pdf_url'];

    // Accessors
    public function getPdfUrlAttribute()
    {
        return url('/api/v1/departures/' . $this->id . '/pdf');
    }

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
        return $query->where('approval_status', false);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', true);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('departure_date', today());
    }

    public function setIceSupplyAttribute($value)
    {
        $this->attributes['ice_supply'] = $value ?? 0;
    }

    public function setWaterSupplyAttribute($value)
    {
        $this->attributes['water_supply'] = $value ?? 0;
    }

    public function setDieselSupplyAttribute($value)
    {
        $this->attributes['diesel_supply'] = $value ?? 0;
    }

    public function setOilSupplyAttribute($value)
    {
        $this->attributes['oil_supply'] = $value ?? 0;
    }

    public function setGasolineSupplyAttribute($value)
    {
        $this->attributes['gasoline_supply'] = $value ?? 0;
    }

    public function setCrewCountAttribute($value)
    {
        $this->attributes['crew_count'] = $value ?? 0;
    }

    public function setEtmalDaysAttribute($value)
    {
        $this->attributes['etmal_days'] = $value ?? 0;
    }
}