<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('property_address_id')->constrained('addresses')->restrictOnDelete();
            $table->string('reference_id')->nullable();
            $table->string('name');
            $table->string('status')->default('lead');
            $table->string('type');
            $table->string('property_type');
            $table->string('loss_type');
            $table->string('claim_number')->nullable();
            $table->string('policy_number')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->date('date_of_loss')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'reference_id']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'location_id']);
            $table->index(['company_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
