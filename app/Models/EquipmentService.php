<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentService extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'renter_name',
        'vessel_id',
        'service_date',
        'start_time',
        'end_time',
        'duration',
        'officer',
        'treasurer',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'service_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the vessel that owns the service.
     */
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    /**
     * Get the equipment items for this service.
     */
    public function items(): HasMany
    {
        return $this->hasMany(EquipmentItem::class);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'order' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'processed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        };
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'order' => 'Pesanan',
            'processed' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    /**
     * Get progress percentage based on status
     */
    public function getProgressPercentage(): int
    {
        return match($this->status) {
            'order' => 25,
            'processed' => 75,
            'completed' => 100,
            'cancelled' => 100,
            default => 0,
        };
    }

    /**
     * Get progress label based on status
     */
    public function getProgressLabel(): string
    {
        return match($this->status) {
            'order' => 'Pesanan Masuk',
            'processed' => 'Sedang Diproses',
            'completed' => 'Telah Selesai',
            'cancelled' => 'Dibatalkan',
            default => 'Unknown',
        };
    }
}
