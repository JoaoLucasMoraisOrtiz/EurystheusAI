<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->unique();
            $table->string('reason', 255);
            $table->enum('block_type', ['TEMPORARY', 'PERMANENT'])->default('TEMPORARY');
            $table->timestamp('blocked_at');
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('blocked_by')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            
            $table->index(['ip', 'is_active']);
            $table->index(['expires_at', 'is_active']);
            $table->index(['block_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
    }
};
