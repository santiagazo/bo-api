<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Jobs table - restoration work orders (inspired by sales_orders from .iris)
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->index()->constrained();
            $table->foreignId('location_id')->nullable()->index()->constrained();
            $table->foreignId('property_address_id')->nullable()->index()->constrained('addresses');
            $table->foreignId('assigned_user_id')->nullable()->index()->constrained('users');
            $table->foreignId('referred_by_organization_id')->nullable()->index()->constrained('organizations');
            $table->foreignId('contract_id')->nullable()->index()->constrained();
            
            // Job details
            $table->string('reference_id')->nullable()->unique();
            $table->string('status')->default('new'); // new, in_progress, completed, cancelled
            $table->string('service_type')->nullable(); // water, fire, mold, storm
            $table->string('lead_source')->nullable(); // vendor_referral, website, direct_call
            $table->text('description')->nullable();
            $table->text('special_instructions')->nullable();
            
            // Financials
            $table->decimal('insurance_estimated_value', 12, 2)->nullable();
            $table->decimal('out_of_pocket_estimated_value', 12, 2)->nullable();
            $table->decimal('actual_value', 12, 2)->nullable();
            $table->decimal('referral_fee_amount', 10, 2)->nullable();
            $table->string('referral_fee_payment_status')->nullable();
            $table->string('payment_status')->nullable();
            
            // Dates
            $table->timestamp('mitigation_started_at')->nullable();
            $table->timestamp('mitigation_completed_at')->nullable();
            $table->date('reconstruction_scheduled_date')->nullable();
            $table->timestamp('reconstruction_completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
