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
        Schema::create('core_flex_field_options', function (Blueprint $table) {
            $table->id();
            $table->string('value',50);

            //belongs to the columns refers from add_foreign_keys_to_some_tables
            $table->unsignedBigInteger('flex_field_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core_flex_field_options');
    }
};
