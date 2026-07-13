<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = ['question', 'answer', 'category', 'priority', 'status'];

    protected $casts = [
        'priority' => 'integer'
    ];

    public function assignments()
    {
        return $this->hasMany(FaqAssignment::class);
    }
}
