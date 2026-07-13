<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifiedEmail extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['email', 'verified_at'];

    protected $casts = [
        'verified_at' => 'datetime'
    ];
}
