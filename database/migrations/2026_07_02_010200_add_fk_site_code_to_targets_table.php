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
        $orphanCount = DB::table('targets')
            ->whereNotIn('site_code', function ($query) {
                $query->select('code')->from('stores');
            })
            ->count();

        if ($orphanCount > 0) {
            throw new \RuntimeException(
                "Migration dibatalkan: ditemukan {$orphanCount} baris di 'targets' ".
                "dengan site_code yang tidak ada di 'stores'. Cek dulu dengan query:\n".
                "SELECT DISTINCT site_code FROM targets WHERE site_code NOT IN (SELECT code FROM stores);"
            );
        }

        Schema::table('targets', function (Blueprint $table) {
            $table->foreign('site_code')
                  ->references('code')
                  ->on('stores')
                  ->onUpdate('cascade')
                  ->onDelete('restrict'); // toko tidak boleh dihapus kalau masih punya target
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->dropForeign(['site_code']);
        });
    }
};
