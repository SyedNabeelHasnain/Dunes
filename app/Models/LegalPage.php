<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'title',
        'subtitle',
        'description',
    ];

    protected $fillable = ['slug', 'title', 'title_ar', 'subtitle', 'subtitle_ar', 'description', 'description_ar'];

    public function sections()
    {
        return $this->hasMany(LegalSection::class, 'page_id')->orderBy('priority', 'asc');
    }
}
