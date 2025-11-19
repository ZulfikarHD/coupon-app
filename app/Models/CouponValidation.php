<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Database\Factories\CouponValidationFactory;

class CouponValidation extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return CouponValidationFactory::new();
    }

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
     * Relationship: The coupon being validated
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Relationship: Staff member who validated
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
