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
        Schema::create('notification_intents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->string('type'); // sms, email, push
            $table->string('destination'); // phone number or email
            $table->string('status')->default('queued'); // queued, sent, delivered, failed
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('attempted_at')->nullable();
            $table->text('receipt')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->boolean('acknowledged')->nullable();
            $table->text('error_message')->nullable();
            $table->jsonb('details')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_intents');
    }
};

