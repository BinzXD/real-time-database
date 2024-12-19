<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDescription extends Model
{
    use HasFactory, KeyGenerate;

    protected $table = 'product_description';

    protected $fillable = [
        'product_id',
        'description',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
