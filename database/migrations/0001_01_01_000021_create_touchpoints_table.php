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
        Schema::create('touchpoints', function (Blueprint $table) {
            $table->id();

            // Polymorphic recipient (Organization, Location, or User)
            $table->string('recipient_type');
            $table->unsignedBigInteger('recipient_id');

            $table->foreignId('touchpoint_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('job_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status')->default('scheduled'); // scheduled, sent, completed, cancelled
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('cost_amount', 10, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['recipient_type', 'recipient_id']);
            $table->index('status');
            $table->index('scheduled_for');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('touchpoints');
    }
};

