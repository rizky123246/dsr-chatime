<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('targets', function (Blueprint $table) {
        $table->id();

        $table->string('site_code'); // relasi ke store
        $table->year('year');
        $table->tinyInteger('month'); // 1-12

        $table->decimal('target_sales', 15, 2)->default(0);
        $table->integer('target_tc')->nullable();
        $table->integer('target_cup')->nullable();

        $table->unsignedBigInteger('created_by');

        $table->timestamps();

        // 🔥 biar tidak double
        $table->unique(['site_code', 'year', 'month']);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
