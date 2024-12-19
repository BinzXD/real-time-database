<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogTag extends Model
{
    use HasFactory, KeyGenerate, SoftDeletes;
    protected $table = 'blog_tags';
    protected $fillable = [
        'name',
        'slug'
    ];
}
