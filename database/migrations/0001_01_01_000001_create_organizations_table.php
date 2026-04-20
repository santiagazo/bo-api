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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('type', 255); // global, vendor, contractor, internal
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_country_code')->nullable();
            $table->foreignId('address_id')->nullable()->constrained();
            $table->decimal('referral_fee_percentage', 5, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};

