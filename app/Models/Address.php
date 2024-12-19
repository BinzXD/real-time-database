<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'receiver',
        'phone',
        'province',
        'city',
        'district',
        'postal_code',
        'address',
        'is_main',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }
}

