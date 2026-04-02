<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Arrival extends Model
{
    use SoftDeletes;

    protected $table = 'arrivals';

    protected $fillable = [
        'vessel_id', 'origin', 'arrival_date', 'arrival_time', 'landing_site_id', 'mutu',
        'fish_quality', 'average_price', 'waste_volume', 'fish_temperature',
        'hold_temperature', 'status', 'approval_status', 'approved_by',
        'approved_at', 'input_by', 'notes', 'is_processed'
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'arrival_time' => 'datetime:H:i:s',
        'average_price' => 'decimal:2',
        'waste_volume' => 'integer',
        'fish_temperature' => 'integer',
        'hold_temperature' => 'integer',
        'approved_at' => 'datetime',
        'is_processed' => 'boolean',
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

    public function catches(): HasMany
    {
        return $this->hasMany(ArrivalCatch::class);
    }

    public function unloading(): HasMany
    {
        return $this->hasMany(Unloading::class);
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
        return $query->whereDate('arrival_date', today());
    }
}
