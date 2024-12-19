<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogVerifications extends Model
{
    use HasFactory, KeyGenerate;
    protected $table = 'log_verification_request';
    protected $fillable = [
        'otp_type_id',
        'phone',
        'code',
        'duration',
        'is_used',
        'is_expired' 
    ];

}
