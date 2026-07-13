<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingAddon extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['booking_id', 'addon_id', 'addon_name', 'quantity', 'price'];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'float'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }
}
