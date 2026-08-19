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
            Schema::create('penjualans', function (Blueprint $table) {
                $table->id();
                
                // Transaction Information
                $table->date('created_date');
                $table->time('created_time');
                $table->string('order_no', 50);
                $table->string('receipt_no', 50);
                $table->string('invoice_reference', 100)->nullable();
                $table->boolean('void')->default(false);
                
                // Site Information
                $table->string('site_code', 20);
                $table->string('site_description', 100);
                
                // Article Information
                $table->string('article_code', 50);
                $table->string('article_name', 150);
                
                // Pricing Information
                $table->integer('quantity');
                $table->decimal('original_price', 15, 2);
                $table->decimal('net_price', 15, 2);
                
                // Promotion Information
                $table->decimal('promotion_amount', 15, 2)->default(0);
                $table->string('promotion_code', 50)->nullable();
                $table->string('promotion_name', 100)->nullable();
                $table->string('promotion_channel', 50)->nullable();
                
                // Department Information
                $table->string('department_code', 20);
                $table->string('department_name', 100);
                
                // Commodity Information
                $table->string('commodity_code', 20);
                $table->string('commodity_name', 100);
                
                // Merchandise Information
                $table->string('merchandise_code', 20);
                $table->string('merchandise_name', 100);
                
                // Product Group Information
                $table->string('product_group_code', 20);
                $table->string('product_group_name', 100);
                
                $table->timestamps();
                
                // Indexes for better performance
                $table->index('created_date');
                $table->index('order_no');
                $table->index('receipt_no');
                $table->index('site_code');
                $table->index('article_code');
                $table->index('department_code');
                $table->index('commodity_code');
                $table->index('merchandise_code');
                $table->index('product_group_code');
            });
        }

        /**
         * Reverse the migrations.
         */
        
        public function down(): void
        {
            Schema::dropIfExists('penjualans');
        }
    };
