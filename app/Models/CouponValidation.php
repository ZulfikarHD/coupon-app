<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CouponValidation extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'validated_by',
        'validated_at',
        'action',
        'notes',
    ];
}
