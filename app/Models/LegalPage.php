<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'title', 'title_ar', 'subtitle', 'subtitle_ar', 'description', 'description_ar'];

    public function sections()
    {
        return $this->hasMany(LegalSection::class, 'page_id')->orderBy('priority', 'asc');
    }
}
