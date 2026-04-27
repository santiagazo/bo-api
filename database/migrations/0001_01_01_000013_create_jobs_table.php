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
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('referred_by_organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('reference_id')->nullable()->unique();
            $table->string('job_name')->nullable();
            $table->string('status')->default('new');
            $table->string('service_type')->nullable();
            $table->string('loss_type')->nullable();
            $table->string('billing_status')->default('draft');
            $table->string('bill_to_type')->nullable();
            $table->string('claim_number')->nullable();
            $table->string('policy_number')->nullable();
            $table->string('lead_source')->nullable();
            $table->text('description')->nullable();
            $table->text('special_instructions')->nullable();
            $table->decimal('actual_value', 12, 2)->nullable();
            $table->string('payment_status')->nullable();
            $table->date('date_of_loss')->nullable();
            $table->timestamp('mitigation_started_at')->nullable();
            $table->timestamp('mitigation_completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('story_summary')->nullable();
            $table->text('adjuster_summary')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
