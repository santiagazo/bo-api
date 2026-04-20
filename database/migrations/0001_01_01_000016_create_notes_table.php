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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->string('noteable_type'); // Job, User, Organization, Location, etc.
            $table->unsignedBigInteger('noteable_id');
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->string('view_level')->default('internal'); // private, internal, vendor, customer, public
            $table->timestamps();
            $table->softDeletes();

            $table->index(['noteable_type', 'noteable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};

