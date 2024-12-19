<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory, KeyGenerate;

    protected $fillable = [
        'product_id',          
        'url',         
        'file_type', 
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
