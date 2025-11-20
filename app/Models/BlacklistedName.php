<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BlacklistedName extends Model
{
    protected $fillable = [
        'name',
        'reason',
    ];

    /**
     * Check if a name is blacklisted (exact match, case-insensitive)
     */
    public static function isBlacklisted(string $name): bool
    {
        // Normalize the name: trim and lowercase
        $normalizedName = strtolower(trim($name));
        
        // Cache blacklisted names for 1 hour to reduce database queries
        $blacklistedNames = Cache::remember('blacklisted_names', 3600, function () {
            return self::pluck('name')->map(fn($n) => strtolower(trim($n)))->toArray();
        });
        
        return in_array($normalizedName, $blacklistedNames);
    }

    /**
     * Clear the cached blacklisted names
     */
    public static function clearCache(): void
    {
        Cache::forget('blacklisted_names');
    }

    /**
     * Boot method to clear cache when model is saved or deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
