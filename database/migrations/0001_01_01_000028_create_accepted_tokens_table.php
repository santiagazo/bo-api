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
        Schema::create('accepted_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_token_id')->constrained()->cascadeOnDelete();

            // Polymorphic recipient (Organization, Location, or User)
            $table->string('recipient_type');
            $table->unsignedBigInteger('recipient_id');

            $table->timestamp('awarded_at')->useCurrent();
            $table->timestamp('fulfilled_at')->nullable();
            $table->string('fulfillment_status')->default('pending'); // pending, processing, fulfilled, failed
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id']);
            $table->index('fulfillment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accepted_tokens');
    }
};

