<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalItem extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = [
        'content',
    ];

    protected $fillable = ['section_id', 'content', 'content_ar', 'priority'];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function section()
    {
        return $this->belongsTo(LegalSection::class, 'section_id');
    }
}
