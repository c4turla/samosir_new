<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishSpecies extends Model
{
    protected $table = 'fish_species';

    protected $fillable = [
        'name', 'scientific_name', 'code', 'category'
    ];

    // Relationships
    public function arrivalCatches(): HasMany
    {
        return $this->hasMany(ArrivalCatch::class);
    }
}