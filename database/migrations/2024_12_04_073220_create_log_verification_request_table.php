<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_verification_request', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('otp_type_id');
            $table->string('phone', 50);
            $table->string('code', 50);
            $table->time('duration');
            $table->boolean('is_used');
            $table->boolean('is_expired');
            $table->timestamps();

            $table->foreign('otp_type_id')->references('id')->on('otp_types')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_verification_request');
    }
};
