<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialMedia extends Model
{
    use HasFactory, KeyGenerate, SoftDeletes;
    protected $table = 'social_media';
    protected $fillable = [
        'logo',
        'name',
        'link',
    ];
}
