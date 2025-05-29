<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_audit_log', function (Blueprint $table) {
            $table->id();
            $table->timestamp('timestamp');
            $table->string('ip', 45); // IPv6 support
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('method', 10);
            $table->text('url');
            $table->text('user_agent')->nullable();
            $table->integer('status_code');
            $table->float('response_time'); // in milliseconds
            $table->integer('request_size')->default(0);
            $table->integer('response_size')->default(0);
            $table->string('session_id')->nullable();
            $table->string('route_name')->nullable();
            $table->json('headers')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['ip', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status_code', 'created_at']);
            $table->index('timestamp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_audit_log');
    }
};
