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
        Schema::create('stock_orders', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // The user placing the order
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade'); // The vendor receiving the order
            $table->string('order_number')->unique(); // Unique order identifier
            $table->enum('status', ['pending', 'approved', 'in_transit', 'delivered', 'completed', 'cancelled'])->default('pending'); // Status of the order
            $table->decimal('total_amount', 10, 2); // Total amount of the order
            $table->timestamp('delivery_date')->nullable(); // Expected or actual delivery date
            $table->text('delivery_address'); // Delivery address
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_orders');
    }
};
