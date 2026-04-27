<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('legal_name')->nullable();
            $table->string('slug')->unique();
            $table->string('status')->default('active');
            $table->string('primary_phone')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('phone_country_code')->nullable();
            $table->string('website_url')->nullable();
            $table->string('timezone')->default('America/Denver');
            $table->string('currency_code', 3)->default('USD');
            $table->string('default_locale', 10)->default('en-US');
            $table->foreignId('address_id')->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
