<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prompt_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anonymous_user')->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->text('content');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('prompt_logs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prompt_logs');
    }
};
