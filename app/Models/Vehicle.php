<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'model_name',
        'plate_number',
        'plate_code',
        'plate_city',
        'year',
        'passenger_capacity',
        'color',
        'status',
        'insurance_expiry_date',
        'mulkiya_expiry_date',
        'dtcm_permit_number',
        'default_driver_id',
        'features',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'passenger_capacity' => 'integer',
        'insurance_expiry_date' => 'date',
        'mulkiya_expiry_date' => 'date',
        'features' => 'array',
    ];

    public function defaultDriver()
    {
        return $this->belongsTo(Driver::class, 'default_driver_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'vehicle_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->model_name} ({$this->plate_city} {$this->plate_code}-{$this->plate_number})";
    }

    public function getShortPlateAttribute(): string
    {
        $city = $this->plate_city ? "{$this->plate_city} " : '';
        return trim("{$city}{$this->plate_code} {$this->plate_number}");
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
