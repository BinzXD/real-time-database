<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes, KeyGenerate;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'parent_id',
        'image',
        'slug',
    ];

    public function subCategories()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
}