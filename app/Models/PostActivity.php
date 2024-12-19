<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostActivity extends Model
{
    use HasFactory, KeyGenerate, SoftDeletes;
    protected $table = 'post_activities';
    protected $fillable = [
        'post_id',
        'ip',
        'userAgent'
    ];
}
