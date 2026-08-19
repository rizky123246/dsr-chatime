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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2);
            $table->string('order_no', 50);
            $table->string('receipt_no', 50);
            $table->string('invoice_reference', 100)->nullable();
            $table->string('mop_code', 20);
            $table->string('mop_name', 100);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('order_no');
            $table->index('receipt_no');
            $table->index('mop_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
