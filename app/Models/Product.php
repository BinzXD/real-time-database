<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, KeyGenerate, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'subcategory_id',
        'sku',
        'price',
        'weight',
        'price_id',
        'discount',
        'point',
        'minimal_order',
        'condition',
        'status',
        'type',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function level()
    {
        return $this->belongsTo(Price::class, 'price_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function description()
    {
        return $this->hasOne(ProductDescription::class);
    }
}
