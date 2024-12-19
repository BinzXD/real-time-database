<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, SoftDeletes, KeyGenerate;
    protected $table = 'banners';
    protected $fillable = [
        'name',
        'image',
        'start_date',
        'end_date',
        'status',
        'link',
        'sequence'
    ];
}
