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
        // 1) Cek dulu apakah ada site_code di penjualans yang tidak
        //    ditemukan di master stores. Kalau ada, migration akan
        //    berhenti di sini sebelum memasang FK, supaya kamu bisa
        //    perbaiki datanya dulu (bukan silent failure di tengah).
        $orphanCount = DB::table('penjualans')
            ->whereNotIn('site_code', function ($query) {
                $query->select('code')->from('stores');
            })
            ->count();

        if ($orphanCount > 0) {
            throw new \RuntimeException(
                "Migration dibatalkan: ditemukan {$orphanCount} baris di 'penjualans' ".
                "dengan site_code yang tidak ada di 'stores'. Perbaiki atau hapus dulu ".
                "baris tersebut sebelum menjalankan migration ini. Cek dengan query:\n".
                "SELECT DISTINCT site_code FROM penjualans WHERE site_code NOT IN (SELECT code FROM stores);"
            );
        }

        Schema::table('penjualans', function (Blueprint $table) {
            $table->foreign('site_code')
                  ->references('code')
                  ->on('stores')
                  ->onUpdate('cascade')
                  ->onDelete('restrict'); // toko tidak boleh dihapus kalau masih punya transaksi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropForeign(['site_code']);
        });
    }
};
