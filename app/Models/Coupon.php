<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'description',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_social_media',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
