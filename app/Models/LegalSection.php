<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalSection extends Model
{
    use HasFactory;

    protected $fillable = ['page_id', 'heading', 'subheading', 'priority'];

    protected $casts = [
        'priority' => 'integer'
    ];

    public function page()
    {
        return $this->belongsTo(LegalPage::class, 'page_id');
    }

    public function items()
    {
        return $this->hasMany(LegalItem::class, 'section_id')->orderBy('priority', 'asc');
    }
}
