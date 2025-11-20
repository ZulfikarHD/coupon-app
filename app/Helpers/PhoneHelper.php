<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Normalize phone number to 628xx format (international format without +)
     * Examples:
     * - 08123456789 → 628123456789
     * - 8123456789 → 628123456789
     * - 628123456789 → 628123456789 (already normalized)
     */
    public static function normalize(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Convert to international format
        if (substr($phone, 0, 1) === '0') {
            // 08123456789 → 628123456789
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            // 8123456789 → 628123456789
            $phone = '62' . $phone;
        }
        
        return $phone;
    }

    /**
     * Format phone number for display (628123456789 → 0812-3456-789)
     */
    public static function formatForDisplay(string $phone): string
    {
        // Convert from international to local format
        if (substr($phone, 0, 2) === '62') {
            $phone = '0' . substr($phone, 2);
        }
        
        // Format: 0812-3456-789
        if (strlen($phone) >= 10) {
            return substr($phone, 0, 4) . '-' . substr($phone, 4, 4) . '-' . substr($phone, 8);
        }
        
        return $phone;
    }

    /**
     * Normalize phone for search (handles partial matches)
     * Used when searching by phone number
     */
    public static function normalizeForSearch(string $phone): string
    {
        return self::normalize($phone);
    }
}
