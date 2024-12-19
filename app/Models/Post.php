<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, KeyGenerate, SoftDeletes;
    protected $table = 'posts';
    protected $fillable = [
        'title',
        'slug',
        'content',
        'user_id',
        'category_id',
        'thumbnail',
        'published_at',
        'status',
        'meta_title',
        'meta_description'
    ];
}
