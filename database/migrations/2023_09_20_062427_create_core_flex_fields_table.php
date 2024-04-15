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
        Schema::create('core_flex_fields', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->string('field_code');
            $table->string('field_label');
            $table->string('data_type');
            $table->string('default_value')->nullable();
            $table->boolean('is_mandatory')->default(0)->nullable();
            $table->boolean('is_enabled')->default(0)->nullable();
            $table->boolean('is_permanent')->default(0)->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core_flex_fields');
    }
};
