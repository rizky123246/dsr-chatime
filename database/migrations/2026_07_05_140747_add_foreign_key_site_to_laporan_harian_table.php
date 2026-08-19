<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $orphanCount = DB::table('laporan_harian')
            ->whereNotIn('site', function ($query) {
                $query->select('code')->from('stores');
            })
            ->count();

        if ($orphanCount > 0) {
            throw new \RuntimeException(
                "Migration dibatalkan: ditemukan {$orphanCount} baris di 'laporan_harian' ".
                "dengan site yang tidak ada di 'stores'. Cek dulu dengan query:\n".
                "SELECT DISTINCT site FROM laporan_harian WHERE site NOT IN (SELECT code FROM stores);"
            );
        }

        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->foreign('site')
                  ->references('code')
                  ->on('stores')
                  ->onUpdate('cascade')
                  ->onDelete('restrict'); // toko tidak boleh dihapus kalau masih punya laporan harian
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropForeign(['site']);
        });
    }
};
