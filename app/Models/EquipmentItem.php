<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_service_id',
        'equipment_name',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Get the equipment service that owns the item.
     */
    public function equipmentService(): BelongsTo
    {
        return $this->belongsTo(EquipmentService::class);
    }

    /**
     * Get equipment name label in Indonesian
     */
    public function getEquipmentLabel(): string
    {
        return match($this->equipment_name) {
            'keranjang_plastik' => 'Keranjang Plastik',
            'meja_sortir' => 'Meja Sortir',
            'gerobak' => 'Gerobak',
            'timbangan' => 'Timbangan',
            'ice_cruiser' => 'Ice Cruiser',
            default => $this->equipment_name,
        };
    }
}
