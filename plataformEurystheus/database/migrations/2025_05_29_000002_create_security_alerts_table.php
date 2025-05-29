<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100);
            $table->enum('threat_level', ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL', 'INFO', 'UNKNOWN'])->default('UNKNOWN');
            $table->string('ip', 45);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('url');
            $table->text('user_agent')->nullable();
            $table->json('details')->nullable();
            $table->boolean('resolved')->default(false);
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['event_type', 'created_at']);
            $table->index(['threat_level', 'created_at']);
            $table->index(['ip', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['resolved', 'threat_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
    }
};
