<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    public function up()
    {
        Schema::create('address', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('customers')->onDelete('cascade');
            $table->string('label', 100);
            $table->string('receiver', 100);
            $table->string('phone', 20);
            $table->string('province', 100);
            $table->string('city', 100);
            $table->string('district', 100);
            $table->string('postal_code', 20);
            $table->text('address');
            $table->boolean('is_main');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('address');
    }
}
