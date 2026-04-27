<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('display_name');
            $table->string('legal_name')->nullable();
            $table->string('status')->default('active');
            $table->string('website_url')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('primary_phone')->nullable();
            $table->string('phone_country_code')->nullable();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
