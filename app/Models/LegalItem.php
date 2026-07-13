<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalItem extends Model
{
    use HasFactory;

    protected $fillable = ['section_id', 'content', 'priority'];

    protected $casts = [
        'priority' => 'integer'
    ];

    public function section()
    {
        return $this->belongsTo(LegalSection::class, 'section_id');
    }
}
