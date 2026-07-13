<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqAssignment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['faq_id', 'entity_type', 'entity_id'];

    public function faq()
    {
        return $this->belongsTo(Faq::class);
    }
}
