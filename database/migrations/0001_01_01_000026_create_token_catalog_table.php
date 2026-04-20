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
        Schema::create('token_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('vendor_category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('retail_value', 10, 2)->nullable();
            $table->decimal('default_unit_cost', 10, 2)->nullable();
            $table->string('fulfillment_type')->default('deliver'); // deliver, ship, pickup, digital
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_catalog');
    }
};

