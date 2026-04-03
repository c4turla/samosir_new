<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'group', 'key', 'value', 'type', 'label', 'description', 'options', 'sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null): ?string
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, $value): bool
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        $setting->update(['value' => $value]);

        Cache::forget("setting.{$key}");
        Cache::forget('settings.all');
        Cache::forget("settings.group.{$setting->group}");

        return true;
    }

    /**
     * Get all settings grouped.
     */
    public static function allGrouped(): array
    {
        return Cache::remember('settings.all', 3600, function () {
            return static::orderBy('group')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('group')
                ->map(function ($items) {
                    return $items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'key' => $item->key,
                            'value' => $item->value,
                            'type' => $item->type,
                            'label' => $item->label,
                            'description' => $item->description,
                            'options' => $item->options ? json_decode($item->options, true) : null,
                        ];
                    });
                })
                ->toArray();
        });
    }

    /**
     * Get all settings for a specific group.
     */
    public static function getGroup(string $group): array
    {
        return Cache::remember("settings.group.{$group}", 3600, function () use ($group) {
            return static::where('group', $group)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'key' => $item->key,
                        'value' => $item->value,
                        'type' => $item->type,
                        'label' => $item->label,
                        'description' => $item->description,
                        'options' => $item->options ? json_decode($item->options, true) : null,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get all settings as a flat key => value array.
     */
    public static function allFlat(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    /**
     * Flush all settings cache.
     */
    public static function flushCache(): void
    {
        $keys = static::pluck('key')->toArray();
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }

        $groups = static::distinct('group')->pluck('group')->toArray();
        foreach ($groups as $group) {
            Cache::forget("settings.group.{$group}");
        }

        Cache::forget('settings.all');
    }
}
