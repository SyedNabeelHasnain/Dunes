<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPostFaq extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['post_id', 'question', 'answer', 'priority'];

    protected $casts = [
        'priority' => 'integer'
    ];

    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }
}
