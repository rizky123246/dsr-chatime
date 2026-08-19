<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_metrics', function (Blueprint $table) {

            // Tambah kolom yang hilang
            $table->foreignId('laporan_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('laporan_harian')
                  ->onDelete('cascade');

            $table->enum('category', [
                'ALL','INSTORE','OJOL','APPS'
            ])->default('ALL');

            $table->enum('channel', [
                'ALL','INSTORE','GOJEK','GRAB','SHOPEE','APP'
            ])->default('ALL');

            $table->enum('period', [
                'CURRENT','LW','LM','LY', 'MTD'
            ])->default('CURRENT');

            $table->enum('metric', [
                'SALES','TC','SC','LARGE','REGULER','SMALL',
                'COLD','HOT','BUTTERFLY','PC','EXTRA LARGE',
                'TOPPING','FOOD', 'TIME_PROGRESS'
            ]);

            $table->decimal('value', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('laporan_metrics', function (Blueprint $table) {
            $table->dropColumn([
                'laporan_id','category','channel','period','metric','value'
            ]);
        });
    }
};