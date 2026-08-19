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
        Schema::create('laporan_harian', function (Blueprint $table) {
            $table->id();
        
            $table->string('site'); // Site
            $table->string('store'); // Store name
            $table->date('trans_date');
            $table->year('year');
        
            $table->decimal('target', 15, 2)->default(0);

            // 🔥 STATUS VALIDASI
            $table->enum('status', ['submitted', 'approved', 'rejected'])
                  ->default('submitted');
    
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
    
            $table->timestamps();
            
            $table->unique(['site', 'trans_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_harian');
    }
};
