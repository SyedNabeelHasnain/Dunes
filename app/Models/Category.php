<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'name', 'icon', 'priority'];

    public function tours()
    {
        return $this->hasMany(Tour::class)->orderBy('priority', 'asc');
    }
}
