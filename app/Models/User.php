<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'nip', 'phone', 'address',
        'photo', 'role', 'is_active', 'email_verified_at'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function vessels(): BelongsToMany
    {
        return $this->belongsToMany(Vessel::class, 'vessel_managers')
                    ->withPivot('is_primary', 'id_card', 'authorization_letter')
                    ->withTimestamps()
                    ->wherePivotNull('deleted_at');
    }

    public function primaryVessels(): BelongsToMany
    {
        return $this->belongsToMany(Vessel::class, 'vessel_managers')
                    ->withPivot('is_primary')
                    ->wherePivot('is_primary', true);
    }

    public function registeredVessels(): HasMany
    {
        return $this->hasMany(Vessel::class, 'registered_by');
    }

    public function approvedVessels(): HasMany
    {
        return $this->hasMany(Vessel::class, 'approved_by');
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }
}