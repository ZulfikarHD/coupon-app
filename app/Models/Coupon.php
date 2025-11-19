<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Coupon extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_USED = 'used';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'code',
        'type',
        'description',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_social_media',
        'expires_at',
        'status',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    /**
     * Normalize phone number to 628xx format
     */
    public function setCustomerPhoneAttribute($value)
    {
        $phone = preg_replace('/[^0-9]/', '', $value);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        $this->attributes['customer_phone'] = $phone;
    }

    /**
     * Get formatted phone number for display (628123456789 → 0812-3456-789)
     */
    public function getFormattedPhoneAttribute(): string
    {
        $phone = $this->customer_phone;
        if (substr($phone, 0, 2) === '62') {
            $phone = '0' . substr($phone, 2);
        }
        if (strlen($phone) >= 10) {
            return substr($phone, 0, 4) . '-' . substr($phone, 4, 4) . '-' . substr($phone, 8);
        }
        return $phone;
    }

    /**
     * Relationship to user who created the coupon
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship to coupon validations
     */
    public function validations()
    {
        return $this->hasMany(CouponValidation::class);
    }

    /**
     * Scope: Active coupons
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Used coupons
     */
    public function scopeUsed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_USED);
    }

    /**
     * Scope: Expired coupons
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }
}
