<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpType extends Model
{
    use HasFactory, KeyGenerate;
    protected $table = 'otp_types';
    protected $fillable = [
        'name',
    ];
}
