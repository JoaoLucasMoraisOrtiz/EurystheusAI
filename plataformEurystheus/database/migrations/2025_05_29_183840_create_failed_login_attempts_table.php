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
        Schema::create('failed_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->index(); // IPv4 or IPv6
            $table->string('email', 255)->nullable()->index();
            $table->string('user_agent', 500)->nullable();
            $table->string('attempted_password', 100)->nullable(); // Hashed for analysis
            $table->json('request_data')->nullable(); // Additional request information
            $table->string('session_id', 100)->nullable()->index();
            $table->string('country_code', 2)->nullable()->index(); // Geographic location
            $table->string('city', 100)->nullable();
            $table->boolean('is_blocked_ip')->default(false)->index();
            $table->boolean('triggered_lockout')->default(false);
            $table->string('attack_pattern', 50)->nullable()->index(); // brute_force, credential_stuffing, etc.
            $table->integer('attempts_in_window')->default(1); // Number of attempts in time window
            $table->timestamp('lockout_until')->nullable()->index();
            $table->timestamps();
            
            // Indexes for performance and security analysis
            $table->index(['ip_address', 'created_at']);
            $table->index(['email', 'created_at']);
            $table->index(['is_blocked_ip', 'created_at']);
            $table->index(['attack_pattern', 'created_at']);
            $table->index(['triggered_lockout', 'lockout_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_login_attempts');
    }
};
