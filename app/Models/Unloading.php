<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unloading extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'arrival_id',
        'reference_number',
        'syahbandar_id',
        'captain_name',
        'identification_mark',
        'unloading_date',
        'unloading_time',
        'sequence_number',
        'registration_date',
        'bl_code',
        'landing_site_id',
        'approval_status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'unloading_date' => 'date',
        'registration_date' => 'date',
        'unloading_time' => 'datetime:H:i',
        'approval_status' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function arrival()
    {
        return $this->belongsTo(Arrival::class);
    }

    public function landingSite()
    {
        return $this->belongsTo(LandingSite::class);
    }

    public function syahbandar()
    {
        return $this->belongsTo(User::class, 'syahbandar_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeFilter($query, $search = null, $status = null, $dateFrom = null, $dateTo = null)
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('bl_code', 'like', "%{$search}%")
                  ->orWhereHas('arrival.vessel', function ($q) use ($search) {
                      $q->where('vessel_name', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($status) {
            $query->where('approval_status', $status);
        }

        if ($dateFrom) {
            $query->where('unloading_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('unloading_date', '<=', $dateTo);
        }

        return $query;
    }
}
