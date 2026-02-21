<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vessel extends Model
{
    use SoftDeletes;

    protected $table = 'vessels';

    protected $fillable = [
        'vessel_name', 'owner_name', 'license_number', 'gt', 'fishing_gear',
        'selar_mark', 'vessel_type', 'sipi_date', 'sipi_end_date', 'length',
        'loa', 'siup_number', 'vessel_photo', 'qr_code', 'registered_by',
        'approval_status', 'approved_by', 'approved_at', 'notes'
    ];

    protected $casts = [
        'sipi_date' => 'date',
        'sipi_end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected $appends = ['sipi_status', 'sipi_status_text', 'vessel_photo_url'];

    // Accessors
    public function getSipiStatusAttribute()
    {
        if (!$this->sipi_end_date) {
            return 'unknown';
        }
        
        return $this->sipi_end_date->isFuture() ? 'active' : 'expired';
    }

    public function getSipiStatusTextAttribute()
    {
        return match($this->sipi_status) {
            'active' => 'Aktif',
            'expired' => 'Expired',
            default => 'Tidak Ada'
        };
    }

    public function getSipiStatusClassAttribute()
    {
        return match($this->sipi_status) {
            'active' => 'green',
            'expired' => 'red',
            default => 'gray'
        };
    }

    public function getVesselPhotoUrlAttribute()
    {
        if (!$this->vessel_photo) {
            return null;
        }
        
        // If it's already a full URL, return it as is
        if (str_starts_with($this->vessel_photo, 'http://') || str_starts_with($this->vessel_photo, 'https://')) {
            return $this->vessel_photo;
        }
        
        // Otherwise, prepend the public storage URL
        return asset('storage/' . $this->vessel_photo);
    }

    // Relationships
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'vessel_managers')
                    ->withPivot('is_primary', 'address', 'id_card', 'authorization_letter')
                    ->withTimestamps()
                    ->wherePivotNull('deleted_at');
    }

    public function primaryManager(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'vessel_managers')
                    ->withPivot('is_primary')
                    ->wherePivot('is_primary', true);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function arrivals(): HasMany
    {
        return $this->hasMany(Arrival::class);
    }

    public function departures(): HasMany
    {
        return $this->hasMany(Departure::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function waterServices(): HasMany
    {
        return $this->hasMany(WaterService::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeByManager($query, $userId)
    {
        return $query->whereHas('managers', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
}