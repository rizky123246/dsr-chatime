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
        $orphanCount = DB::table('penjualans')
            ->whereNotIn('article_code', function ($query) {
                $query->select('article_code')->from('products');
            })
            ->count();

        if ($orphanCount > 0) {
            throw new \RuntimeException(
                "Migration dibatalkan: ditemukan {$orphanCount} baris di 'penjualans' ".
                "dengan article_code yang tidak ada di 'products'. Ini sangat mungkin terjadi ".
                "karena data penjualans berasal dari import Excel/POS yang tidak selalu sinkron ".
                "dengan master produk. Cek dulu dengan query:\n".
                "SELECT DISTINCT article_code FROM penjualans WHERE article_code NOT IN (SELECT article_code FROM products);\n".
                "Lalu tambahkan artikel yang hilang ke 'products', atau pertimbangkan onDelete('set null') ".
                "kalau memang ada artikel lama yang sudah discontinued."
            );
        }

        Schema::table('penjualans', function (Blueprint $table) {
            $table->foreign('article_code')
                  ->references('article_code')
                  ->on('products')
                  ->onUpdate('cascade')
                  ->onDelete('restrict'); // produk tidak boleh dihapus kalau masih ada transaksi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropForeign(['article_code']);
        });
    }
};
