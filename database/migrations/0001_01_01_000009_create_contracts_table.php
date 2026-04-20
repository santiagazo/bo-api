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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('created_from')->nullable()->constrained('contracts')->nullOnDelete();
            // Fee structures TBD - placeholder for now
            $table->jsonb('fee_details')->default('{}');
            $table->date('effective_date');
            $table->datetime('expired_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index(['organization_id', 'expired_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

