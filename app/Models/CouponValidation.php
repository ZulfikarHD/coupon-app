<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CouponValidation extends Model
{
    use HasFactory;

    public const ACTION_USED = 'used';
    public const ACTION_REVERSED = 'reversed';

    protected $fillable = [
        'coupon_id',
        'validated_by',
        'validated_at',
        'action',
        'notes',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    /**
     * Relationship to coupon
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Relationship to user who validated
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
