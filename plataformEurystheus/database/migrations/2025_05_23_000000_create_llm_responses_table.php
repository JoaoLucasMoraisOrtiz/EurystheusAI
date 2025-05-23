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
        Schema::create('llm_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_log_id')->constrained('prompt_logs')->onDelete('cascade');
            $table->text('llm_reasoning')->nullable();
            $table->text('generated_prompts')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_responses');
    }
};
