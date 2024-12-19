<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
