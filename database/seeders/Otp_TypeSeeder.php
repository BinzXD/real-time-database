<?php

namespace Database\Seeders;

use App\Models\OtpType;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class Otp_TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OtpType::insert([
            [
                'id' => Str::uuid(),
                'name' => 'verify',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'name' => 'forgotPassword',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
