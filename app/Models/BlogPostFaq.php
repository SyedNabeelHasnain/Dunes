<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPostFaq extends Model
{
    use HasFactory, HasTranslations;

    public $timestamps = false;

    public array $translatable = [
        'question',
        'answer',
    ];

    protected $fillable = ['post_id', 'question', 'answer', 'priority'];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }
}
