<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishSpecies extends Model
{
    protected $table = 'fish_species';

    protected $fillable = [
        'species_name', 'local_name', 'scientific_name', 'category', 'is_active'
    ];

    // Relationships
    public function arrivalCatches(): HasMany
    {
        return $this->hasMany(ArrivalCatch::class);
    }
}