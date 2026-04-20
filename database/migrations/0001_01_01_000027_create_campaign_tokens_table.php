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
        Schema::create('campaign_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appreciation_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('token_catalog_id')->constrained('token_catalog')->restrictOnDelete();
            $table->unsignedInteger('quantity_total')->default(0);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->jsonb('eligibility_rules')->nullable();
            $table->timestamps();

            $table->unique(['appreciation_campaign_id', 'token_catalog_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_tokens');
    }
};

