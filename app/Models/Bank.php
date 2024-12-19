<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use HasFactory, KeyGenerate, SoftDeletes;
    protected $table = 'banks';
    protected $fillable = [
        'code',
        'name',
        'icon',
        'alias',
        'status'
    ];
}
