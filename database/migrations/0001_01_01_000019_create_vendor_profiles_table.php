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
        Schema::create('vendor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('tier')->default('prospect'); // prospect, partner, referral_tree, elite
            $table->string('status')->default('trial_period'); // trial_period, approved, flagged, paused, do_not_contact

            $table->string('participation_scope')->default('organization'); // organization, location, individual
            $table->boolean('is_gateway')->default(false);

            $table->string('preferred_contact_channel')->default('sms'); // sms, email, call

            $table->foreignId('primary_contact_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('primary_location_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_profiles');
    }
};

