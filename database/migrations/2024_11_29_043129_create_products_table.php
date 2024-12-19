<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 200);
            $table->string('slug', 200)->unique();

            $table->uuid('category_id');
            $table->uuid('subcategory_id')->nullable();
            $table->uuid('price_id');

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('subcategory_id')->references('id')->on('categories');
            $table->foreign('price_id')->references('id')->on('prices');

            $table->string('sku', 100)->unique();
            $table->decimal('price');
            $table->decimal('weight');
            $table->decimal('discount')->nullable();
            $table->integer('point')->nullable();
            $table->integer('minimal_order');
            $table->enum('condition', ['new', 'second']);
            $table->enum('status', ['ready stock', 'preorder']);
            $table->enum('type', ['no_variant', 'variant']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
