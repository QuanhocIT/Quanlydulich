<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    protected $fillable = [
        'destination_id',
        'name',
        'slug',
        'description',
        'itinerary',
        'price',
        'price_sale',
        'duration_days',
        'max_guests',
        'difficulty',
        'tour_type',
        'image',
        'images',
        'includes',
        'excludes',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_sale' => 'decimal:2',
        'images' => 'array',
        'includes' => 'array',
        'excludes' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        return $this->price_sale ?? $this->price;
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->where('is_approved', true)->avg('rating') ?? 0;
    }
}
