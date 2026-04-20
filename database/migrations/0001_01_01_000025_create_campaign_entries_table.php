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
        Schema::create('campaign_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appreciation_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('engagement_event_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('entries')->default(1);
            $table->timestamp('created_at')->useCurrent();

            $table->index('appreciation_campaign_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_entries');
    }
};

