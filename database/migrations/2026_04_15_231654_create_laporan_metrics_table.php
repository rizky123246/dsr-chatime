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
        Schema::create('laporan_metrics', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('laporan_id')
                  ->constrained('laporan_harian')
                  ->onDelete('cascade');
        
            // =============================
            // DIMENSI UTAMA
            // =============================
        
            $table->string('category')->default('ALL');
            $table->string('channel')->default('ALL');
        
            $table->enum('period', [
                'CURRENT', // hari ini
                'LW',      // last week
                'LM',      // last month
                'LY',       // last year
                'MTD'
            ])->default('CURRENT');

        
            
            
            // =============================
            // METRIC SESUAI KEBUTUHAN KAMU
            // =============================
        
            $table->enum('metric', [
                // CURRENT
                'SALES',
                'TC',
                'SC',
                'AVG_CHECK',
            
                // MTD
                'TIME_PROGRESS',
                'MTD_SALES',
                'TARGET_SALES',
                'ACHIEVEMENT',
            
                // LW
                'SALES_LW',
                'TC_LW',
                'AVG_CHECK_LW',
                'GROWTH_LW',
                'GROWTH_TC_LW',
                'GROWTH_AVG_LW',
            
                // LM
                'SALES_LM',
                'TC_LM',
                'AVG_CHECK_LM',
                'GROWTH_LM',
                'GROWTH_TC_LM',
                'GROWTH_AVG_LM',
            
                // CUP
                'LARGE',
                'REGULER',
                'SMALL',
                'COLD',
                'HOT',
                'BUTTERFLY',
                'PC',
                'EXTRA_LARGE',
                'TOTAL_CUP',
            
                // OJOL
                'TOTAL_OJOL',
            
                // 
                'FOOD',
                'QTY'
            ]);
        
            $table->decimal('value', 15, 2)->default(0);
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
        public function down(): void
        {
            Schema::dropIfExists('laporan_metrics');
        }
    };
