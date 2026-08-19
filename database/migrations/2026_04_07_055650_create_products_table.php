<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('article_code')->unique(); // dari excel (Article)
            $table->string('name'); // Nama produk
            $table->string('size')->nullable(); // R / L
            $table->string('type'); // Drink / Merch
            $table->string('category')->nullable(); // Series
            $table->string('brand')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};