<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_metrics', function (Blueprint $table) {
            $table->string('metric')->change();
        });
    }

    public function down(): void
    {
        //
    }
};