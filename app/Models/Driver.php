<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Driver extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'license_number',
        'phone',
        'email',
        'photo',
        'languages',
        'status',
        'default_vehicle_id',
        'emergency_contact_name',
        'emergency_contact_phone',
        'license_expiry_date',
        'visa_expiry_date',
        'rating',
        'total_trips',
        'access_token',
        'notes',
    ];

    protected $casts = [
        'languages' => 'array',
        'license_expiry_date' => 'date',
        'visa_expiry_date' => 'date',
        'rating' => 'float',
        'total_trips' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($driver) {
            if (empty($driver->access_token)) {
                $driver->access_token = Str::random(32);
            }
        });
    }

    public function defaultVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'default_vehicle_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'driver_id');
    }

    public function todayBookings()
    {
        return $this->hasMany(Booking::class, 'driver_id')
            ->whereDate('tour_date', now()->format('Y-m-d'))
            ->whereIn('status', ['confirmed', 'completed', 'pending']);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getCleanPhoneAttribute(): string
    {
        return preg_replace('/[^0-9]/', '', $this->phone);
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && (str_starts_with($this->photo, 'http://') || str_starts_with($this->photo, 'https://'))) {
            return $this->photo;
        }

        if ($this->photo && file_exists(public_path($this->photo))) {
            return asset($this->photo);
        }

        return asset('images/safari-captain-avatar.avif');
    }

    public function getLanguagesListAttribute(): string
    {
        if (is_array($this->languages) && !empty($this->languages)) {
            return implode(', ', $this->languages);
        }

        return 'English, Arabic';
    }
}
