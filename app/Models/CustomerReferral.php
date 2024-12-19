<?php

namespace App\Models;

use App\Traits\KeyGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReferral extends Model
{
    use HasFactory, KeyGenerate;
    protected $table = 'customer_referral';
    protected $fillable = ['customer_id'];
}
