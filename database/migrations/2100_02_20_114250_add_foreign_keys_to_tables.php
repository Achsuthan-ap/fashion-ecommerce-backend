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
        Schema::table('core_flex_values', function (Blueprint $table) {
            $table->foreign('entity_id')->references('id')->on('core_entities');
        });
        Schema::table('core_flex_field_options', function (Blueprint $table) {
            $table->foreign('flex_field_id')->references('id')->on('core_flex_fields')->onDelete('cascade');
        });
        Schema::table('product_categories', function (Blueprint $table) {
            $table->foreign('entity_id')->references('id')->on('core_entities')->onDelete('cascade');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('entity_id')->references('id')->on('core_entities')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('cascade');
            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('core_flex_values', function (Blueprint $table) {
            $table->dropForeign(['entity_id']);
        });
        Schema::table('core_flex_field_options', function (Blueprint $table) {
            $table->dropForeign(['flex_field_id']);
        });
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropForeign(['entity_id']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['entity_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['offer_id']);
        });
    }
};
