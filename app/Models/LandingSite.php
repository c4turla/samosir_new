<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingSite extends Model
{
    protected $table = 'landing_sites';

    protected $fillable = [
        'site_name', 'address', 'distance', 'latitude', 'longitude', 'site_type', 'is_active'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function arrivals(): HasMany
    {
        return $this->hasMany(Arrival::class);
    }

    public function departures(): HasMany
    {
        return $this->hasMany(Departure::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}