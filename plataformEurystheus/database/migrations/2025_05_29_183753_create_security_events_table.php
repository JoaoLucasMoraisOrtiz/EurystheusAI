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
        Schema::create('security_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100)->index(); // Type of security event
            $table->string('severity', 20)->default('medium')->index(); // low, medium, high, critical
            $table->string('source_ip', 45)->index(); // IPv4 or IPv6
            $table->string('user_agent', 500)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('method', 10)->nullable(); // HTTP method
            $table->json('payload')->nullable(); // Request payload for analysis
            $table->json('headers')->nullable(); // Request headers
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('session_id', 100)->nullable()->index();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional event data
            $table->string('status', 20)->default('detected')->index(); // detected, blocked, resolved
            $table->boolean('automated_response')->default(false);
            $table->string('response_action', 100)->nullable(); // Action taken
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['event_type', 'created_at']);
            $table->index(['severity', 'status']);
            $table->index(['source_ip', 'created_at']);
            $table->index(['user_id', 'created_at']);
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_events');
    }
};
