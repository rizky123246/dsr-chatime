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
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('site_code')
                  ->references('code')
                  ->on('stores')
                  ->onUpdate('cascade')
                  ->onDelete('set null'); // atau cascade kalau mau ikut kehapus
        });
    }
    
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['site_code']);
        });
    }
};
