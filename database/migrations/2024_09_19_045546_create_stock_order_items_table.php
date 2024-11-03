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
        Schema::create('stock_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_order_id')->constrained('stock_orders')->onDelete('cascade'); // The order this item belongs to
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // The product being ordered
            $table->integer('quantity'); // Quantity ordered
            $table->decimal('price', 10, 2); // Price at time of ordering
            $table->decimal('subtotal', 10, 2); // Subtotal for this order item
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_order_items');
    }
};