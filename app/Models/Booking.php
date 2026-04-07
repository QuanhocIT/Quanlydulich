<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'booking_code',
        'user_id',
        'tour_id',
        'departure_date',
        'num_adults',
        'num_children',
        'total_price',
        'status',
        'payment_status',
        'payment_method',
        'contact_name',
        'contact_email',
        'contact_phone',
        'special_requests',
        'admin_notes',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'total_price' => 'decimal:2',
        'num_adults' => 'integer',
        'num_children' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = strtoupper('BK-' . Str::random(8));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function review(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class);
    }
}
