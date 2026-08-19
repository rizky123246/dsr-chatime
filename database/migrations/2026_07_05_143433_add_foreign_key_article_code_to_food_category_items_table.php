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
        $orphanCount = DB::table('food_category_items')
            ->whereNotIn('article_code', function ($query) {
                $query->select('article_code')->from('products');
            })
            ->count();

        if ($orphanCount > 0) {
            throw new \RuntimeException(
                "Migration dibatalkan: ditemukan {$orphanCount} baris di 'food_category_items' ".
                "dengan article_code yang tidak ada di 'products'. Cek dulu dengan query:\n".
                "SELECT * FROM food_category_items WHERE article_code NOT IN (SELECT article_code FROM products);"
            );
        }

        Schema::table('food_category_items', function (Blueprint $table) {
            $table->foreign('article_code')
                  ->references('article_code')
                  ->on('products')
                  ->onUpdate('cascade')
                  ->onDelete('restrict'); // produk tidak boleh dihapus kalau masih dipakai di mapping kategori food
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_category_items', function (Blueprint $table) {
            $table->dropForeign(['article_code']);
        });
    }
};