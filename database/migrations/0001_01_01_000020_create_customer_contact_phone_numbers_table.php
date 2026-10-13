<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_contact_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_contact_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('mobile');
            $table->string('phone_number');
            $table->string('phone_country_code')->default('US');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_contact_phone_numbers');
    }
};
