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
            ->whereNotIn('created_by', function ($query) {
                $query->select('id')->from('users');
            })
            ->count();

        if ($orphanCount > 0) {
            throw new \RuntimeException(
                "Migration dibatalkan: ditemukan {$orphanCount} baris di 'targets' ".
                "dengan created_by yang tidak menunjuk ke user manapun. Cek dulu dengan query:\n".
                "SELECT DISTINCT created_by FROM targets WHERE created_by NOT IN (SELECT id FROM users);"
            );
        }

        // created_by awalnya unsignedBigInteger dan tidak nullable.
        // Diubah jadi nullable dulu supaya bisa pakai onDelete('set null')
        // -- kalau user yang membuat target dihapus, target tetap ada,
        // hanya penunjuk pembuatnya yang kosong.
        Schema::table('targets', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('targets', function (Blueprint $table) {
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('targets', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
        });
    }
};
