<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArrivalCatch extends Model
{
    protected $table = 'arrival_catches';

    protected $fillable = [
        'arrival_id', 'fish_species_id', 'weight_kg', 'estimated_value'
    ];

    protected $casts = [
        'weight_kg' => 'integer',
        'estimated_value' => 'decimal:2',
    ];

    // Relationships
    public function arrival(): BelongsTo
    {
        return $this->belongsTo(Arrival::class);
    }

    public function fishSpecies(): BelongsTo
    {
        return $this->belongsTo(FishSpecies::class, 'fish_species_id');
    }
}