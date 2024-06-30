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
            // Drop existing foreign key if exists
            $table->dropForeign(['entity_id']);

            // Add new foreign key with unique name
            $table->foreign('entity_id', 'fk_core_flex_values_entity_id')->references('id')->on('core_entities');
        });

        Schema::table('core_flex_field_options', function (Blueprint $table) {
            $table->foreign('flex_field_id', 'fk_core_flex_field_options_flex_field_id')->references('id')->on('core_flex_fields')->onDelete('cascade');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->foreign('entity_id', 'fk_product_categories_entity_id')->references('id')->on('core_entities')->onDelete('cascade');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('entity_id', 'fk_products_entity_id')->references('id')->on('core_entities')->onDelete('cascade');
            $table->foreign('category_id', 'fk_products_category_id')->references('id')->on('product_categories')->onDelete('cascade');
            $table->foreign('offer_id', 'fk_products_offer_id')->references('id')->on('offers')->onDelete('cascade');
        });

        Schema::table('cart', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_cart_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id', 'fk_cart_product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('core_flex_values', function (Blueprint $table) {
            $table->dropForeign('fk_core_flex_values_entity_id');
        });
        Schema::table('core_flex_field_options', function (Blueprint $table) {
            $table->dropForeign('fk_core_flex_field_options_flex_field_id');
        });
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropForeign('fk_product_categories_entity_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('fk_products_entity_id');
            $table->dropForeign('fk_products_category_id');
            $table->dropForeign('fk_products_offer_id');
        });
        Schema::table('cart', function (Blueprint $table) {
            $table->dropForeign('fk_cart_user_id');
            $table->dropForeign('fk_cart_product_id');
        });
    }
};
