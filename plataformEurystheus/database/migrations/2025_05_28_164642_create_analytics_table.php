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
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // 'page_view', 'button_click', etc.
            $table->string('page')->nullable(); // 'home', 'sales', etc.
            $table->string('element')->nullable(); // button id/class for clicks
            $table->string('url')->nullable(); // full URL
            $table->string('referrer')->nullable(); // where user came from
            $table->string('user_agent')->nullable(); // browser info
            $table->string('ip_address')->nullable(); // user IP
            $table->string('session_id')->nullable(); // session tracking
            $table->json('metadata')->nullable(); // additional data
            $table->timestamps();
            
            $table->index(['event_type', 'created_at']);
            $table->index(['page', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics');
    }
};
